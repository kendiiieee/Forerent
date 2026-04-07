<?php

namespace App\Livewire\Layouts\Units;

use App\Livewire\Concerns\InspectionConfig;
use App\Livewire\Concerns\WithContractData;
use App\Livewire\Concerns\WithESignature;
use App\Models\ContractAuditLog;
use App\Models\Lease;
use App\Models\MoveOutInspection;
use App\Models\Notification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;

class LandlordContractViewer extends Component
{
    use WithESignature, WithContractData;

    public $showModal = false;
    public $contractType = 'move-in';
    public $contractData = null;
    public $leaseId = null;

    // Move-in signature display
    public $tenantSignature = null;
    public $ownerSignature = null;
    public $managerSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignedAt = null;
    public $managerSignedAt = null;
    public $contractAgreed = false;

    // Move-out signature display
    public $moveOutTenantSignature = null;
    public $moveOutOwnerSignature = null;
    public $moveOutManagerSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutManagerSignedAt = null;
    public $moveOutContractAgreed = false;

    // Signature modals
    public $showSignatureModal = false;
    public $showMoveOutSignatureModal = false;

    // Inspection data
    public $inspectionChecklist = [];
    public $itemsReceived = [];
    public $moveOutChecklist = [];
    public $itemsReturned = [];
    public $inspectionSaved = false;
    public $moveOutInspectionSaved = false;

    #[On('open-landlord-contract-viewer')]
    public function openViewer(int $leaseId, string $contractType = 'move-in'): void
    {
        $this->contractType = $contractType;
        $this->leaseId = $leaseId;

        $lease = Lease::with([
            'tenant',
            'bed.unit.property.owner',
            'billings',
            'moveInInspections',
            'moveOutInspections',
        ])->find($leaseId);

        if (!$lease || !$lease->tenant) {
            return;
        }

        $this->contractData = $this->buildContractDataArray($lease->tenant, $lease);
        $this->loadSignatureState($lease);

        // Load move-in inspection
        $this->loadInspection(
            $lease,
            'moveInInspections',
            'inspectionChecklist',
            'itemsReceived',
            'inspectionSaved',
            'item_received',
            InspectionConfig::RECEIVED_ITEMS
        );

        // Load move-out inspection
        $this->loadInspection(
            $lease,
            'moveOutInspections',
            'moveOutChecklist',
            'itemsReturned',
            'moveOutInspectionSaved',
            'item_returned',
            InspectionConfig::RETURNED_ITEMS
        );

        $this->showModal = true;
    }

    public function switchTab(string $type): void
    {
        $this->contractType = $type;
    }

    // ===== OWNER SIGNING (Move-in) =====

    public function openSignatureModal(): void
    {
        $this->showSignatureModal = true;
    }

    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
    }

    public function saveOwnerSignature(string $signatureData): void
    {
        if (!$this->leaseId) return;

        $lease = Lease::find($this->leaseId);
        if (!$lease) return;

        // Verify the authenticated user is the property owner
        $ownerId = $this->findOwnerIdForLease($lease);
        if (Auth::id() !== $ownerId) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign this contract.');
            return;
        }

        $result = $this->saveLeaseSignature($lease, $signatureData, 'owner', 'movein');

        $this->ownerSignature = $result['signature'];
        $this->ownerSignedAt = $result['signedAt'];
        $this->contractAgreed = $result['agreed'];

        // Notify manager that owner signed → manager's turn to sign as witness
        $this->notifyManagerOfOwnerSign($lease, 'move-in');

        // Update signature_info in contractData
        $lease->refresh();
        $this->contractData['signature_info'] = [
            'tenant_signature'     => $lease->tenant_signature,
            'tenant_signed_at'     => $lease->tenant_signed_at?->format('M d, Y h:i A'),
            'owner_signature'      => $lease->owner_signature,
            'owner_signed_at'      => $lease->owner_signed_at?->format('M d, Y h:i A'),
            'manager_signature'    => $lease->manager_signature,
            'manager_signed_at'    => $lease->manager_signed_at?->format('M d, Y h:i A'),
            'contract_agreed'      => (bool) $lease->contract_agreed,
            'signed_contract_path' => $lease->signed_contract_path,
        ];

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Signature Saved', description: 'You have signed the move-in contract as the property owner.');
    }

    // ===== OWNER SIGNING (Move-out) =====

    public function openMoveOutSignatureModal(): void
    {
        // Refresh outstanding balances to ensure real-time accuracy before signing
        $lease = Lease::find($this->leaseId);
        if ($lease) {
            $this->contractData['outstanding_balances'] = $this->buildOutstandingBalances($lease);
            $this->contractData['deposit_refund'] = [
                'amount' => $lease->deposit_refund_amount,
                'deductions' => $lease->deposit_deductions,
                'interest_earned' => $lease->deposit_interest_amount,
            ];
        }

        // Block signing if there are TBD (unconfirmed) repair/replacement costs
        $hasTBD = MoveOutInspection::where('lease_id', $this->leaseId)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('type', 'checklist')
                       ->whereIn('condition', ['damaged', 'missing'])
                       ->where(function ($q3) {
                           $q3->whereNull('repair_cost')->orWhere('repair_cost', 0);
                       });
                })
                ->orWhere(function ($q2) {
                    $q2->where('type', 'item_returned')
                       ->where('is_returned', false)
                       ->where(function ($q3) {
                           $q3->whereNull('replacement_cost')->orWhere('replacement_cost', 0);
                       });
                });
            })
            ->exists();

        if ($hasTBD) {
            $this->dispatch('notify', type: 'error', title: 'Cannot Sign Yet', description: 'All repair and replacement costs must be confirmed before signing. No TBD values allowed.');
            return;
        }

        $this->showMoveOutSignatureModal = true;
    }

    public function closeMoveOutSignatureModal(): void
    {
        $this->showMoveOutSignatureModal = false;
    }

    public function saveMoveOutOwnerSignature(string $signatureData): void
    {
        if (!$this->leaseId) return;

        $lease = Lease::find($this->leaseId);
        if (!$lease) return;

        $ownerId = $this->findOwnerIdForLease($lease);
        if (Auth::id() !== $ownerId) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign this contract.');
            return;
        }

        $result = $this->saveLeaseSignature($lease, $signatureData, 'owner', 'moveout');

        $this->moveOutOwnerSignature = $result['signature'];
        $this->moveOutOwnerSignedAt = $result['signedAt'];
        $this->moveOutContractAgreed = $result['agreed'];

        // Notify manager that owner signed → manager's turn to sign as witness
        $this->notifyManagerOfOwnerSign($lease, 'move-out');

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Signature Saved', description: 'You have signed the move-out contract as the property owner.');
    }

    // ===== DOWNLOAD PDF =====

    public function downloadContract()
    {
        if ($this->contractType === 'move-out') {
            return $this->downloadMoveOutContract();
        }

        return $this->downloadMoveInContract();
    }

    public function downloadMoveInContract()
    {
        $lease = Lease::with(['tenant', 'bed.unit.property'])->find($this->leaseId);
        if (!$lease) return;

        if ($lease->signed_contract_path && Storage::disk('public')->exists($lease->signed_contract_path)) {
            $tenant = $lease->tenant;
            return Storage::disk('public')->download(
                $lease->signed_contract_path,
                'Move-In-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . ($lease->bed->unit->unit_number ?? 'N-A') . '.pdf'
            );
        }

        $t = $this->contractData;
        $rate = (float) ($t['move_in_details']['monthly_rate'] ?? 0);
        $deposit = (float) ($t['move_in_details']['security_deposit'] ?? 0);
        $premium = (float) ($t['move_in_details']['short_term_premium'] ?? 0);
        $dueDay = $t['move_in_details']['monthly_due_date'] ?? null;
        $dueSfx = match ((int) $dueDay) { 1, 21, 31 => 'st', 2, 22 => 'nd', 3, 23 => 'rd', default => 'th' };

        $property = $lease->bed?->unit?->property;
        $contractSettings = $property?->contract_settings ?? [];

        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        $govIdImage = $lease->tenant?->government_id_image;
        $govIdBase64 = null;
        if ($govIdImage) {
            if (Storage::disk('local')->exists($govIdImage)) {
                $govIdBase64 = 'data:image/png;base64,' . base64_encode(Storage::disk('local')->get($govIdImage));
            } elseif (Storage::disk('public')->exists($govIdImage)) {
                $govIdBase64 = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($govIdImage));
            }
        }

        $data = [
            'tenant'                 => $t,
            'lessor'                 => $t['lessor_info'],
            't'                      => $t,
            'tenantSignatureBase64'  => $this->resolveSignatureBase64($lease->tenant_signature),
            'ownerSignatureBase64'   => $this->resolveSignatureBase64($lease->owner_signature),
            'managerSignatureBase64' => $this->resolveSignatureBase64($lease->manager_signature),
            'tenantSignedAt'         => $lease->tenant_signed_at?->format('M d, Y'),
            'ownerSignedAt'          => $lease->owner_signed_at?->format('M d, Y'),
            'managerSignedAt'        => $lease->manager_signed_at?->format('M d, Y'),
            'managerName'            => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Unit Manager',
            'contractSettings'       => $contractSettings,
            'inspectionChecklist'    => [],
            'itemsReceived'          => $this->itemsReceived ?? [],
            'rate'                   => $rate,
            'deposit'                => $deposit,
            'premium'                => $premium,
            'dueDay'                 => $dueDay,
            'dueSfx'                 => $dueSfx,
            'govIdBase64'            => $govIdBase64,
        ];

        $pdf = Pdf::loadView('pdf.move-in-contract', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        // Cache the generated PDF for future downloads
        $cachePath = 'contracts/move-in-' . $lease->id . '.pdf';
        Storage::disk('public')->put($cachePath, $pdf->output());
        $lease->update(['signed_contract_path' => $cachePath]);

        $tenant = $lease->tenant;
        $filename = 'Move-In-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . ($lease->bed->unit->unit_number ?? 'N-A') . '.pdf';

        return Storage::disk('public')->download($cachePath, $filename);
    }

    public function downloadMoveOutContract()
    {
        $lease = Lease::with(['tenant', 'bed.unit.property'])->find($this->leaseId);
        if (!$lease) return;

        if ($lease->moveout_signed_contract_path && Storage::disk('public')->exists($lease->moveout_signed_contract_path)) {
            $tenant = $lease->tenant;
            return Storage::disk('public')->download(
                $lease->moveout_signed_contract_path,
                'Move-Out-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . ($lease->bed->unit->unit_number ?? 'N-A') . '.pdf'
            );
        }

        $t = $this->contractData;
        $deposit = (float) ($t['move_in_details']['security_deposit'] ?? 0);

        $property = $lease->bed?->unit?->property;
        $contractSettings = $property?->contract_settings ?? [];

        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        $data = [
            'tenant'                 => $t,
            't'                      => $t,
            'deposit'                => $deposit,
            'moveOutChecklist'       => $this->moveOutChecklist ?? [],
            'itemsReturned'          => $this->itemsReturned ?? [],
            'inspectionChecklist'    => $this->inspectionChecklist ?? [],
            'tenantSignatureBase64'  => $this->resolveSignatureBase64($lease->moveout_tenant_signature),
            'ownerSignatureBase64'   => $this->resolveSignatureBase64($lease->moveout_owner_signature),
            'managerSignatureBase64' => $this->resolveSignatureBase64($lease->moveout_manager_signature),
            'tenantSignedAt'         => $lease->moveout_tenant_signed_at?->format('M d, Y'),
            'ownerSignedAt'          => $lease->moveout_owner_signed_at?->format('M d, Y'),
            'managerSignedAt'        => $lease->moveout_manager_signed_at?->format('M d, Y'),
            'managerName'            => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Unit Manager',
            'contractSettings'       => $contractSettings,
            'outstandingBalances'    => $t['outstanding_balances'] ?? [],
            'depositRefund'          => $t['deposit_refund'] ?? [],
        ];

        $pdf = Pdf::loadView('pdf.move-out-contract', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        // Cache the generated PDF for future downloads
        $cachePath = 'contracts/move-out-' . $lease->id . '.pdf';
        Storage::disk('public')->put($cachePath, $pdf->output());
        $lease->update(['moveout_signed_contract_path' => $cachePath]);

        $tenant = $lease->tenant;
        $filename = 'Move-Out-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . ($lease->bed->unit->unit_number ?? 'N-A') . '.pdf';

        return Storage::disk('public')->download($cachePath, $filename);
    }

    private function resolveSignatureBase64(?string $relativePath): ?string
    {
        if (!$relativePath) return null;

        if (Storage::disk('local')->exists($relativePath)) {
            return 'data:image/png;base64,' . base64_encode(Storage::disk('local')->get($relativePath));
        }
        if (Storage::disk('public')->exists($relativePath)) {
            return 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($relativePath));
        }

        return null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->contractData = null;
    }

    public function render()
    {
        return view('livewire.layouts.units.landlord-contract-viewer');
    }
}
