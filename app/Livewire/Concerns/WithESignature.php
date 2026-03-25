<?php

namespace App\Livewire\Concerns;

use App\Models\Lease;
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
        $prefix = $type === 'moveout' ? 'moveout_' : '';
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

        $lease->update([
            $sigField => $filename,
            $dateField => now(),
            $ipField => request()->ip(),
        ]);

        $lease->refresh();

        // Check if both signatures exist for this contract type
        if ($lease->$tenantSigField && $lease->$ownerSigField) {
            $lease->update([$agreedField => true]);
        }

        return [
            'signature' => $filename,
            'signedAt' => now()->format('M d, Y h:i A'),
            'agreed' => (bool) ($lease->$tenantSigField && $lease->$ownerSigField),
        ];
    }
}
