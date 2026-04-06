<?php

namespace App\Livewire\Concerns;

use App\Models\ContractAuditLog;
use App\Models\Lease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait WithESignature
{
    /**
     * Process base64 signature data, store as PNG, and return the storage path.
     * Deletes the old signature file if one exists.
     */
    protected function processAndStoreSignature(
        string $signatureData,
        string $filenamePrefix,
        ?string $oldSignaturePath = null
    ): string {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
        $imageData = base64_decode($imageData);

        $filename = "signatures/{$filenamePrefix}_" . time() . '.png';
        Storage::disk('public')->put($filename, $imageData);

        if ($oldSignaturePath) {
            Storage::disk('public')->delete($oldSignaturePath);
        }

        return $filename;
    }

    /**
     * Save a signature to a lease for a given contract type and role.
     *
     * @param Lease  $lease        The lease to update
     * @param string $signatureData Base64 encoded signature image
     * @param string $role         'tenant' or 'owner'
     * @param string $type         'movein' or 'moveout'
     * @return array{signature: string, signedAt: string} The saved signature path and formatted date
     */
    protected function saveLeaseSignature(
        Lease $lease,
        string $signatureData,
        string $role,
        string $type = 'movein'
    ): array {
        $dbPrefix = $type === 'moveout' ? 'moveout_' : '';
        $sigField = $dbPrefix . ($role === 'tenant' ? 'tenant_signature' : 'owner_signature');
        $dateField = $dbPrefix . ($role === 'tenant' ? 'tenant_signed_at' : 'owner_signed_at');
        $ipField = $dbPrefix . ($role === 'tenant' ? 'tenant_signed_ip' : 'owner_signed_ip');
        $agreedField = $dbPrefix . 'contract_agreed';
        $tenantSigField = $dbPrefix . 'tenant_signature';
        $ownerSigField = $dbPrefix . 'owner_signature';

        $filename = $this->processAndStoreSignature(
            $signatureData,
            "{$lease->lease_id}_{$type}_{$role}",
            $lease->$sigField
        );

        // Use a transaction with row lock to prevent race condition
        // when both parties sign simultaneously
        $agreed = DB::transaction(function () use ($lease, $filename, $sigField, $dateField, $ipField, $agreedField, $tenantSigField, $ownerSigField, $role, $type) {
            $locked = Lease::where('lease_id', $lease->lease_id)->lockForUpdate()->first();

            $locked->update([
                $sigField => $filename,
                $dateField => now(),
                $ipField => request()->ip(),
            ]);

            $locked->refresh();

            // Check if both signatures exist for this contract type
            $bothSigned = $locked->$tenantSigField && $locked->$ownerSigField;

            // Auto-transition contract status (move-in contracts only)
            if ($type === 'movein') {
                if ($bothSigned) {
                    $locked->update([
                        $agreedField => true,
                        'contract_status' => 'executed',
                    ]);
                } elseif ($role === 'tenant') {
                    // Tenant signed first → waiting for owner
                    if ($locked->contract_status !== 'executed') {
                        $locked->update(['contract_status' => 'pending_owner']);
                    }
                } elseif ($role === 'owner') {
                    // Owner signed first → waiting for tenant
                    if ($locked->contract_status !== 'executed') {
                        $locked->update(['contract_status' => 'pending_tenant']);
                    }
                }
            } else {
                // Move-out contract: track status + set agreed
                if ($bothSigned) {
                    $locked->update([
                        $agreedField => true,
                        'moveout_contract_status' => 'executed',
                    ]);
                } elseif ($role === 'tenant') {
                    if ($locked->moveout_contract_status !== 'executed') {
                        $locked->update(['moveout_contract_status' => 'pending_owner']);
                    }
                } elseif ($role === 'owner') {
                    if ($locked->moveout_contract_status !== 'executed') {
                        $locked->update(['moveout_contract_status' => 'pending_tenant']);
                    }
                }
            }

            // Audit log
            ContractAuditLog::log($locked->lease_id, "{$type}_signature_{$role}", [
                'field_changed' => $sigField,
                'new_value' => $filename,
                'metadata' => [
                    'contract_type' => $type,
                    'role' => $role,
                    'ip' => request()->ip(),
                    'both_signed' => $bothSigned,
                ],
            ]);

            if ($bothSigned) {
                ContractAuditLog::log($locked->lease_id, "{$type}_contract_executed", [
                    'metadata' => ['contract_type' => $type],
                ]);
            }

            return $bothSigned;
        });

        $lease->refresh();

        return [
            'signature' => $filename,
            'signedAt' => now()->format('M d, Y h:i A'),
            'agreed' => $agreed,
        ];
    }
}
