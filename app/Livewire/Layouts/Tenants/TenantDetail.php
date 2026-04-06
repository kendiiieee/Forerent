<?php

namespace App\Livewire\Layouts\Tenants;

use App\Livewire\Concerns\InspectionConfig;
use App\Livewire\Concerns\WithContractData;
use App\Livewire\Concerns\WithESignature;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\ContractAuditLog;
use App\Models\Lease;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\Notification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;

class TenantDetail extends Component
{
    use WithESignature, WithContractData;

    public $currentTenantId = null;
    public $currentTenant = null;
    public $viewingTab = 'current';

    // Move-out modal fields
    public $showMoveInContract = false;
    public $showMoveOutContract = false;

    // E-signature fields (move-in)
    public $showSignatureModal = false;
    public $signatureRole = '';
    public $tenantSignature = null;
    public $ownerSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignedAt = null;
    public $contractAgreed = false;

    // E-signature fields (move-out)
    public $showMoveOutSignatureModal = false;
    public $moveOutSignatureRole = '';
    public $moveOutTenantSignature = null;
    public $moveOutOwnerSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutContractAgreed = false;

    // Move-out form fields
    public $forwardingAddress = '';
    public $reasonForVacating = '';
    public $depositRefundMethod = '';
    public $depositRefundAccount = '';

    // Move-in inspection form
    public $inspectionChecklist = [];
    public $itemsReceived = [];
    public $inspectionSaved = false;
    public $currentLeaseId = null;

    // Move-out inspection form
    public $moveOutChecklist = [];
    public $itemsReturned = [];
    public $moveOutInspectionSaved = false;

    // Move-out workflow
    public $moveOutInitiated = false;
    public $moveOutPrerequisites = [];

    // Violations
    public $violations = [];
    public $violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];

    public function mount(?int $initialTenantId = null): void
    {
        if ($initialTenantId) {
            $this->loadTenant($initialTenantId);
        }
    }

    #[On('tenantSelected')]
    public function loadTenant(int $tenantId, string $tab = 'current', ?int $buildingId = null): void
    {
        $this->viewingTab = $tab;
        $this->showMoveInContract = false;
        $this->showMoveOutContract = false;

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->first();

        if (!$tenant) {
            $this->resetTenantData();
            return;
        }

        if ($tab === 'current' || $tab === 'moving_out') {
            $lease = Lease::where('tenant_id', $tenantId)
                ->where('status', 'Active')
                ->latest()
                ->with([
                    'bed.unit.property',
                    'moveInInspections',
                    'moveOutInspections',
                ])
                ->first();
        } else {
            $leaseQuery = Lease::where('tenant_id', $tenantId)
                ->where('status', 'Expired')
                ->with([
                    'bed.unit.property',
                    'moveInInspections',
                    'moveOutInspections',
                ]);

            if ($buildingId) {
                $leaseQuery->whereHas('bed.unit', fn($q) => $q->where('property_id', $buildingId));
            }

            $lease = $leaseQuery->latest()->first();
        }

        $this->currentTenantId = $tenantId;
        $this->currentLeaseId = $lease?->lease_id;
        $this->currentTenant = $this->buildContractDataArray($tenant, $lease);
        $this->loadSignatureState($lease);

        $this->loadInspectionData($lease);
        $this->loadMoveOutInspectionData($lease);
        $this->loadViolations($lease);
        $this->moveOutInitiated = (bool) $lease?->move_out_initiated_at;
        $this->forwardingAddress = $lease?->forwarding_address ?? '';
        $this->reasonForVacating = $lease?->reason_for_vacating ?? '';
        $this->depositRefundMethod = $lease?->deposit_refund_method ?? '';
        $this->depositRefundAccount = $lease?->deposit_refund_account ?? '';
        $this->computeMoveOutPrerequisites();
    }

    private function loadInspectionData($lease): void
    {
        $this->loadInspection(
            $lease, 'moveInInspections',
            'inspectionChecklist', 'itemsReceived', 'inspectionSaved',
            'item_received', InspectionConfig::RECEIVED_ITEMS
        );
    }

    public function updatedInspectionChecklist($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'condition') {
            $currentIndex = (int) $parts[0];

            // Clear error for the current item since user just selected a condition
            $this->resetErrorBag("inspectionChecklist.{$currentIndex}.condition");

            // Flag any previous items that were skipped (no condition selected)
            for ($i = 0; $i < $currentIndex; $i++) {
                if (empty($this->inspectionChecklist[$i]['condition'])) {
                    $this->addError(
                        "inspectionChecklist.{$i}.condition",
                        "Please select a condition for \"{$this->inspectionChecklist[$i]['item_name']}\"."
                    );
                }
            }
        }
    }

    public function updatedItemsReceived($value, $key): void
    {
        $this->handleItemsUpdate($value, $key, 'itemsReceived', $this->itemsReceived);
        $this->validateSkippedChecklist();
    }

    public function setItemCondition(int $index, string $condition): void
    {
        $this->itemsReceived[$index]['condition'] = $condition;
        $this->handleItemsUpdate($condition, "{$index}.condition", 'itemsReceived', $this->itemsReceived);
        $this->validateSkippedChecklist();
    }

    private function validateSkippedChecklist(): void
    {
        foreach ($this->inspectionChecklist as $i => $item) {
            if (empty($item['condition'])) {
                $this->addError(
                    "inspectionChecklist.{$i}.condition",
                    "Please select a condition for \"{$item['item_name']}\"."
                );
            }
        }
    }

    public function saveInspection(): void
    {
        if (!$this->currentLeaseId) return;

        $errors = $this->validateInspection(
            $this->inspectionChecklist, 'inspectionChecklist',
            $this->itemsReceived, 'itemsReceived'
        );

        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->addError($key, $message);
            }
            $this->dispatch('scroll-to-error');
            return;
        }

        $this->upsertInspection(
            $this->currentLeaseId, MoveInInspection::class,
            $this->inspectionChecklist, $this->itemsReceived, 'item_received'
        );

        // Auto-transition contract status: draft → pending_signatures
        $lease = Lease::find($this->currentLeaseId);
        if ($lease && $lease->contract_status === 'draft') {
            $lease->update(['contract_status' => 'pending_signatures']);
        }

        // Audit log
        ContractAuditLog::log($this->currentLeaseId, 'movein_inspection_saved', [
            'metadata' => [
                'checklist_count' => count($this->inspectionChecklist),
                'items_count' => count($this->itemsReceived),
            ],
        ]);

        // Auto-notify tenant that inspection is ready for review
        if ($lease) {
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'inspection_ready',
                'title' => 'Move-In Inspection Ready',
                'message' => 'Your move-in room inspection has been completed. Please review and confirm the items received.',
                'link' => '/tenant?tab=inspection',
            ]);
        }

        $this->inspectionSaved = true;
        $this->dispatch('inspection-saved');
        $this->dispatch('notify', type: 'success', title: 'Inspection Saved', description: 'Move-in inspection data has been saved to the contract.');
    }

    public function cancelInspection(): void
    {
        if ($this->currentLeaseId) {
            $lease = Lease::with('moveInInspections')->find($this->currentLeaseId);
            $this->loadInspectionData($lease);
        }
        $this->dispatch('inspection-cancelled');
    }

    private function loadMoveOutInspectionData($lease): void
    {
        $this->loadInspection(
            $lease, 'moveOutInspections',
            'moveOutChecklist', 'itemsReturned', 'moveOutInspectionSaved',
            'item_returned', InspectionConfig::RETURNED_ITEMS
        );
    }

    public function updatedMoveOutChecklist($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'condition') {
            $currentIndex = (int) $parts[0];

            $this->resetErrorBag("moveOutChecklist.{$currentIndex}.condition");

            for ($i = 0; $i < $currentIndex; $i++) {
                if (empty($this->moveOutChecklist[$i]['condition'])) {
                    $this->addError(
                        "moveOutChecklist.{$i}.condition",
                        "Please select a condition for \"{$this->moveOutChecklist[$i]['item_name']}\"."
                    );
                }
            }
        }
    }

    public function updatedItemsReturned($value, $key): void
    {
        $this->handleItemsUpdate($value, $key, 'itemsReturned', $this->itemsReturned);
        $this->validateSkippedMoveOutChecklist();
    }

    public function setMoveOutItemCondition(int $index, string $condition): void
    {
        $this->itemsReturned[$index]['condition'] = $condition;
        $this->handleItemsUpdate($condition, "{$index}.condition", 'itemsReturned', $this->itemsReturned);
        $this->validateSkippedMoveOutChecklist();
    }

    private function validateSkippedMoveOutChecklist(): void
    {
        foreach ($this->moveOutChecklist as $i => $item) {
            if (empty($item['condition'])) {
                $this->addError(
                    "moveOutChecklist.{$i}.condition",
                    "Please select a condition for \"{$item['item_name']}\"."
                );
            }
        }
    }

    public function saveMoveOutInspection(): void
    {
        if (!$this->currentLeaseId) return;

        $errors = $this->validateInspection(
            $this->moveOutChecklist, 'moveOutChecklist',
            $this->itemsReturned, 'itemsReturned'
        );

        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->addError($key, $message);
            }
            $this->dispatch('scroll-to-error');
            return;
        }

        $this->upsertInspection(
            $this->currentLeaseId, MoveOutInspection::class,
            $this->moveOutChecklist, $this->itemsReturned, 'item_returned'
        );

        // Audit log
        ContractAuditLog::log($this->currentLeaseId, 'moveout_inspection_saved', [
            'metadata' => [
                'checklist_count' => count($this->moveOutChecklist),
                'items_count' => count($this->itemsReturned),
            ],
        ]);

        $lease = Lease::find($this->currentLeaseId);

        // If signatures exist, reset them since inspection data changed
        if ($lease && ($lease->moveout_tenant_signature || $lease->moveout_owner_signature)) {
            // Delete old signature files
            if ($lease->moveout_tenant_signature) {
                Storage::disk('public')->delete($lease->moveout_tenant_signature);
            }
            if ($lease->moveout_owner_signature) {
                Storage::disk('public')->delete($lease->moveout_owner_signature);
            }

            $lease->update([
                'moveout_tenant_signature' => null,
                'moveout_tenant_signed_at' => null,
                'moveout_tenant_signed_ip' => null,
                'moveout_owner_signature' => null,
                'moveout_owner_signed_at' => null,
                'moveout_owner_signed_ip' => null,
                'moveout_contract_agreed' => false,
                'moveout_contract_status' => 'draft',
                'moveout_signed_contract_path' => null,
            ]);

            $this->moveOutTenantSignature = null;
            $this->moveOutOwnerSignature = null;
            $this->moveOutTenantSignedAt = null;
            $this->moveOutOwnerSignedAt = null;
            $this->moveOutContractAgreed = false;

            ContractAuditLog::log($this->currentLeaseId, 'moveout_signatures_reset', [
                'metadata' => ['reason' => 'Inspection data modified after signing'],
            ]);
        }

        // Auto-notify tenant that move-out inspection is ready
        if ($lease) {
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'inspection_ready',
                'title' => 'Move-Out Inspection Ready',
                'message' => 'Your move-out room inspection has been completed. Please review and confirm the items returned.',
                'link' => '/tenant?tab=inspection',
            ]);
        }

        $this->moveOutInspectionSaved = true;
        $this->computeMoveOutPrerequisites();
        $this->dispatch('moveout-inspection-saved');
        $this->dispatch('notify', type: 'success', title: 'Inspection Saved', description: 'Move-out inspection data has been saved.');
    }

    public function cancelMoveOutInspection(): void
    {
        if ($this->currentLeaseId) {
            $lease = Lease::with('moveOutInspections')->find($this->currentLeaseId);
            $this->loadMoveOutInspectionData($lease);
        }
        $this->dispatch('moveout-inspection-cancelled');
    }

    private function loadViolations($lease): void
    {
        if (!$lease) {
            $this->violations = [];
            $this->violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];
            return;
        }

        $this->violations = DB::table('violations')
            ->where('lease_id', $lease->lease_id)
            ->whereNull('deleted_at')
            ->orderBy('offense_number', 'asc')
            ->get()
            ->map(fn($v) => (array) $v)
            ->toArray();

        $statusCounts = collect($this->violations)->groupBy('status')->map->count();
        $this->violationCounts = [
            'total' => count($this->violations),
            'issued' => $statusCounts->get('Issued', 0),
            'acknowledged' => $statusCounts->get('Acknowledged', 0),
            'resolved' => $statusCounts->get('Resolved', 0),
        ];
    }

    #[On('refresh-violation-list')]
    public function refreshViolations(): void
    {
        if ($this->currentLeaseId) {
            $lease = Lease::find($this->currentLeaseId);
            $this->loadViolations($lease);
        }
    }

    private function resetTenantData(): void
    {
        $this->currentTenantId = null;
        $this->currentTenant   = null;
        $this->currentLeaseId  = null;
        $this->inspectionChecklist = [];
        $this->itemsReceived = [];
        $this->inspectionSaved = false;
        $this->moveOutChecklist = [];
        $this->itemsReturned = [];
        $this->moveOutInspectionSaved = false;
        $this->moveOutTenantSignature = null;
        $this->moveOutOwnerSignature = null;
        $this->moveOutTenantSignedAt = null;
        $this->moveOutOwnerSignedAt = null;
        $this->moveOutContractAgreed = false;
        $this->moveOutInitiated = false;
        $this->moveOutPrerequisites = [];
        $this->violations = [];
        $this->violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];
    }

    public function editTenant(): void
    {
        if ($this->currentTenantId) {
            $this->dispatch('open-edit-tenant-modal', tenantId: $this->currentTenantId);
        }
    }

    public function transferTenant(): void
    {
        if ($this->currentTenantId) {
            $this->dispatch('open-transfer-tenant-modal', tenantId: $this->currentTenantId);
        }
    }

    public function moveOutTenant(): void
    {
        if (!$this->currentTenantId) return;

        // If already initiated, open the confirmation modal
        if ($this->moveOutInitiated) {
            $this->computeMoveOutPrerequisites();
            $this->dispatch('open-modal', 'move-out-confirmation');
            return;
        }

        // Otherwise open the initiation form
        $this->dispatch('open-modal', 'initiate-move-out');
    }

    public function initiateMoveOut(): void
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease || $lease->move_out_initiated_at) return;

        $lease->update([
            'move_out_initiated_at' => now(),
            'forwarding_address' => $this->forwardingAddress ?: null,
            'reason_for_vacating' => $this->reasonForVacating ?: null,
            'deposit_refund_method' => $this->depositRefundMethod ?: null,
            'deposit_refund_account' => $this->depositRefundAccount ?: null,
        ]);

        ContractAuditLog::log($lease->lease_id, 'move_out_initiated', [
            'metadata' => [
                'reason' => $this->reasonForVacating,
            ],
        ]);

        // Notify tenant
        Notification::create([
            'user_id' => $lease->tenant_id,
            'type' => 'move_out_initiated',
            'title' => 'Move-Out Process Started',
            'message' => 'Your move-out process has been initiated by management. Please coordinate for the move-out inspection and clearance.',
            'link' => '/tenant?tab=inspection',
        ]);

        $this->dispatch('close-modal', 'initiate-move-out');
        $this->moveOutInitiated = true;

        // Reload tenant data to unlock the move-out UI
        $this->loadTenant($this->currentTenantId, $this->viewingTab);
        $this->dispatch('notify', type: 'success', title: 'Move-Out Initiated', description: 'The move-out process has been started. You can now complete the inspection and contract.');
    }

    public function saveMoveOutDetails(): void
    {
        if (!$this->currentLeaseId) return;

        Lease::where('lease_id', $this->currentLeaseId)->update([
            'forwarding_address' => $this->forwardingAddress ?: null,
            'reason_for_vacating' => $this->reasonForVacating ?: null,
            'deposit_refund_method' => $this->depositRefundMethod ?: null,
            'deposit_refund_account' => $this->depositRefundAccount ?: null,
        ]);

        $this->dispatch('notify', type: 'success', title: 'Details Saved', description: 'Move-out details have been updated.');
    }

    public function computeMoveOutPrerequisites(): void
    {
        if (!$this->currentLeaseId) {
            $this->moveOutPrerequisites = [];
            return;
        }

        $leaseId = $this->currentLeaseId;

        $unpaidCount = Billing::where('lease_id', $leaseId)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->count();

        $inspectionDone = MoveOutInspection::where('lease_id', $leaseId)
            ->where('type', 'checklist')
            ->exists();

        $itemsReturnedDone = MoveOutInspection::where('lease_id', $leaseId)
            ->where('type', 'item_returned')
            ->exists();

        $lease = Lease::find($leaseId);
        $contractSigned = $lease
            && $lease->moveout_tenant_signature
            && $lease->moveout_owner_signature
            && $lease->moveout_contract_agreed;

        $this->moveOutPrerequisites = [
            ['label' => 'All bills settled', 'done' => $unpaidCount === 0],
            ['label' => 'Move-out inspection completed', 'done' => $inspectionDone],
            ['label' => 'Items returned recorded', 'done' => $itemsReturnedDone],
            ['label' => 'Move-out contract signed by both parties', 'done' => $contractSigned],
        ];
    }

    public function confirmMoveOut(): void
    {
        if (!$this->currentTenantId) return;

        $activeLeases = Lease::where('tenant_id', $this->currentTenantId)
            ->where('status', 'Active')
            ->get(['lease_id', 'bed_id', 'end_date']);

        if ($activeLeases->isEmpty()) {
            $this->dispatch('close-modal', 'move-out-confirmation');
            $this->dispatch('notify',
                type: 'warning',
                title: 'No Active Lease',
                description: 'This tenant has no active lease to move out.'
            );
            return;
        }

        // Check all prerequisites at once
        $this->computeMoveOutPrerequisites();
        $blockers = collect($this->moveOutPrerequisites)->filter(fn($p) => !$p['done']);

        if ($blockers->isNotEmpty()) {
            $blockerList = $blockers->pluck('label')->implode(', ');
            $this->dispatch('notify',
                type: 'error',
                title: 'Prerequisites Not Met',
                description: "Cannot finalize move-out. Incomplete: {$blockerList}"
            );
            return;
        }

        $today = \Carbon\Carbon::today();

        DB::transaction(function () use ($activeLeases, $today) {
            foreach ($activeLeases as $activeLease) {
                $lease = Lease::find($activeLease->lease_id);

                // Capture original end_date BEFORE overwriting for early termination check
                $originalEndDate = $lease->end_date;

                $lease->update([
                    'status'   => 'Expired',
                    'move_out' => $today,
                    'end_date' => $today,
                ]);

                // Auto-calculate deposit refund with original end_date
                $refundData = $lease->calculateDepositRefund($originalEndDate);
                $lease->update([
                    'deposit_refund_amount' => $refundData['refund_amount'],
                    'deposit_deductions' => $refundData['deductions'],
                ]);

                ContractAuditLog::log($lease->lease_id, 'move_out_completed', [
                    'metadata' => [
                        'deposit_refund' => $refundData['refund_amount'],
                        'total_deductions' => $refundData['total_deductions'],
                        'deductions' => $refundData['deductions'],
                        'original_end_date' => $originalEndDate?->format('Y-m-d'),
                    ],
                ]);

                // Notify tenant of move-out and deposit refund
                Notification::create([
                    'user_id' => $lease->tenant_id,
                    'type' => 'move_out_completed',
                    'title' => 'Move-Out Completed',
                    'message' => 'Your move-out has been processed. Deposit refund: PHP ' . number_format($refundData['refund_amount'], 2) . '. Refund will be processed within 30 days.',
                    'link' => '/tenant?tab=inspection',
                ]);
            }

            \App\Models\Bed::whereIn('bed_id', $activeLeases->pluck('bed_id')->filter()->unique())
                ->update(['status' => 'Vacant']);
        });

        $this->dispatch('refresh-tenant-list');
        $this->dispatch('close-modal', 'move-out-confirmation');
        $this->resetTenantData();
        $this->dispatch('notify',
            type: 'success',
            title: 'Tenant Moved Out',
            description: 'Lease marked as expired, deposit refund calculated, and bed status updated.'
        );
    }

    public function openMoveInContract(): void
    {
        $this->showMoveInContract  = true;
        $this->showMoveOutContract = false;
    }

    public function closeMoveInContract(): void
    {
        $this->showMoveInContract = false;
    }

    public function openMoveOutContract(): void
    {
        $this->showMoveOutContract = true;
        $this->showMoveInContract  = false;
    }

    public function closeMoveOutContract(): void
    {
        $this->showMoveOutContract = false;
    }

    /**
     * Verify the authenticated manager is authorized for this lease's unit.
     */
    private function authorizedForLease(): bool
    {
        if (!$this->currentLeaseId) return false;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return false;

        return \App\Models\Unit::where('unit_id', function ($q) use ($lease) {
            $q->select('unit_id')
                ->from('beds')
                ->where('bed_id', $lease->bed_id)
                ->limit(1);
        })->where('manager_id', Auth::id())->exists();
    }

    public function openSignatureModal(string $role): void
    {
        // Manager can only sign as owner/lessor
        if ($role !== 'owner') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $this->signatureRole      = $role;
        $this->showSignatureModal = true;
    }

    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
        $this->signatureRole      = '';
    }

    public function saveSignature(string $signatureData): void
    {
        // Manager can only sign as owner
        if (!$this->currentLeaseId || $this->signatureRole !== 'owner') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, 'owner', 'movein');

        $this->ownerSignature = $result['signature'];
        $this->ownerSignedAt = $result['signedAt'];
        $this->contractAgreed = $result['agreed'];

        // Notify tenant that the manager/owner signed
        $this->notifyTenantOfSign($lease, 'move-in');

        // If both signatures exist, generate PDF and auto-generate billing
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateSignedPdf($lease);
            $this->autoGenerateBillingOnExecution($lease);

            // Notify both parties that contract is fully executed
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'contract_executed',
                'title' => 'Contract Fully Executed',
                'message' => 'Your move-in contract has been signed by both parties and is now active. You can download the signed copy from your dashboard.',
                'link' => '/tenant?tab=inspection',
            ]);
        }

        // Update signature_info in currentTenant
        $lease->refresh();
        $this->currentTenant['signature_info'] = [
            'tenant_signature'     => $lease->tenant_signature,
            'tenant_signed_at'     => $lease->tenant_signed_at?->format('M d, Y h:i A'),
            'owner_signature'      => $lease->owner_signature,
            'owner_signed_at'      => $lease->owner_signed_at?->format('M d, Y h:i A'),
            'contract_agreed'      => (bool) $lease->contract_agreed,
            'signed_contract_path' => $lease->signed_contract_path,
        ];

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Signature Saved', description: 'Move-in contract has been signed by the lessor.');
    }

    /**
     * Auto-generate the first billing when a move-in contract is fully executed,
     * if no billing exists yet for this lease.
     */
    private function autoGenerateBillingOnExecution(Lease $lease): void
    {
        // Skip if billings already exist (created during AddTenantModal)
        if ($lease->billings()->exists()) return;

        $rate = (float) $lease->contract_rate;
        $premium = (float) ($lease->short_term_premium ?? 0);
        $deposit = (float) ($lease->security_deposit ?? 0);
        $dueDate = $lease->monthly_due_date;

        // Calculate next billing and due dates
        $startDate = $lease->start_date ?? now();
        $nextBilling = \Carbon\Carbon::parse($startDate)->addMonth();
        $billingDueDate = $dueDate
            ? \Carbon\Carbon::parse($startDate)->day($dueDate)
            : \Carbon\Carbon::parse($startDate)->addDays(30);

        // Rent billing (advance)
        $billing = Billing::create([
            'lease_id' => $lease->lease_id,
            'billing_type' => 'move_in',
            'billing_date' => $startDate,
            'next_billing' => $nextBilling,
            'due_date' => $billingDueDate,
            'amount' => $rate + $premium,
            'to_pay' => $rate + $premium + $deposit,
            'status' => 'Unpaid',
        ]);

        // Billing items breakdown
        BillingItem::create([
            'billing_id' => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type' => 'advance',
            'description' => '1 Month Advance Rent',
            'amount' => $rate,
        ]);

        if ($premium > 0) {
            BillingItem::create([
                'billing_id' => $billing->billing_id,
                'charge_category' => 'conditional',
                'charge_type' => 'short_term_premium',
                'description' => 'Short-Term Premium',
                'amount' => $premium,
            ]);
        }

        BillingItem::create([
            'billing_id' => $billing->billing_id,
            'charge_category' => 'move_in',
            'charge_type' => 'deposit',
            'description' => 'Security Deposit',
            'amount' => $deposit,
        ]);

        ContractAuditLog::log($lease->lease_id, 'billing_auto_generated', [
            'metadata' => [
                'billing_id' => $billing->billing_id,
                'total' => $rate + $premium + $deposit,
            ],
        ]);
    }

    private function generateSignedPdf(Lease $lease): void
    {
        $lease->load(['tenant', 'bed.unit.property']);

        // Verify signature files exist before attempting to read them
        if (!$lease->tenant_signature || !Storage::disk('public')->exists($lease->tenant_signature)) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Tenant signature file is missing. Cannot generate signed contract PDF.');
            return;
        }
        if (!$lease->owner_signature || !Storage::disk('public')->exists($lease->owner_signature)) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Owner signature file is missing. Cannot generate signed contract PDF.');
            return;
        }

        $tenantSigPath = Storage::disk('public')->path($lease->tenant_signature);
        $ownerSigPath  = Storage::disk('public')->path($lease->owner_signature);

        $data = [
            'tenant'                => $this->currentTenant,
            'lessor'                => $this->currentTenant['lessor_info'],
            't'                     => $this->currentTenant,
            'tenantSignatureBase64' => 'data:image/png;base64,' . base64_encode(file_get_contents($tenantSigPath)),
            'ownerSignatureBase64'  => 'data:image/png;base64,' . base64_encode(file_get_contents($ownerSigPath)),
            'tenantSignedAt'        => $lease->tenant_signed_at->format('M d, Y'),
            'ownerSignedAt'         => $lease->owner_signed_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('pdf.move-in-contract', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $pdfPath = "contracts/lease_{$lease->lease_id}_signed_" . time() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        if ($lease->signed_contract_path) {
            Storage::disk('public')->delete($lease->signed_contract_path);
        }

        $lease->update(['signed_contract_path' => $pdfPath]);
    }

    public function downloadSignedContract()
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->signed_contract_path) return;

        return Storage::disk('public')->download(
            $lease->signed_contract_path,
            'Move-In-Contract_' . ($this->currentTenant['personal_info']['first_name'] ?? '') . '-' . ($this->currentTenant['personal_info']['last_name'] ?? 'Tenant') . '_Unit-' . ($this->currentTenant['personal_info']['unit'] ?? 'N-A') . '.pdf'
        );
    }

    public function openMoveOutSignatureModal(string $role): void
    {
        // Manager can only sign as owner/lessor
        if ($role !== 'owner') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $this->moveOutSignatureRole = $role;
        $this->showMoveOutSignatureModal = true;
    }

    public function closeMoveOutSignatureModal(): void
    {
        $this->showMoveOutSignatureModal = false;
        $this->moveOutSignatureRole      = '';
    }

    public function saveMoveOutSignature(string $signatureData): void
    {
        // Manager can only sign as owner
        if (!$this->currentLeaseId || $this->moveOutSignatureRole !== 'owner') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, 'owner', 'moveout');

        $this->moveOutOwnerSignature = $result['signature'];
        $this->moveOutOwnerSignedAt = $result['signedAt'];
        $this->moveOutContractAgreed = $result['agreed'];

        // Notify tenant that the manager/owner signed
        $this->notifyTenantOfSign($lease, 'move-out');

        // If both signatures exist, generate PDF
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateMoveOutSignedPdf($lease);
        }

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Signature Saved', description: 'Move-out contract has been signed by the lessor.');
    }

    private function generateMoveOutSignedPdf(Lease $lease): void
    {
        $lease->load(['tenant', 'bed.unit.property', 'moveInInspections', 'moveOutInspections']);

        // Verify signature files exist before attempting to read them
        if (!$lease->moveout_tenant_signature || !Storage::disk('public')->exists($lease->moveout_tenant_signature)) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Tenant signature file is missing. Cannot generate signed move-out PDF.');
            return;
        }
        if (!$lease->moveout_owner_signature || !Storage::disk('public')->exists($lease->moveout_owner_signature)) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Owner signature file is missing. Cannot generate signed move-out PDF.');
            return;
        }

        $tenantSigPath = Storage::disk('public')->path($lease->moveout_tenant_signature);
        $ownerSigPath = Storage::disk('public')->path($lease->moveout_owner_signature);

        // Build move-in checklist for comparison
        $moveInChecklist = $lease->moveInInspections
            ->where('type', 'checklist')
            ->map(fn($i) => ['item_name' => $i->item_name, 'condition' => $i->condition, 'remarks' => $i->remarks])
            ->toArray();

        // Build move-out checklist (include repair_cost)
        $moveOutChecklist = $lease->moveOutInspections
            ->where('type', 'checklist')
            ->map(fn($i) => ['item_name' => $i->item_name, 'condition' => $i->condition, 'remarks' => $i->remarks, 'repair_cost' => $i->repair_cost])
            ->toArray();

        // Build items returned (include is_returned + replacement_cost)
        $itemsReturned = $lease->moveOutInspections
            ->where('type', 'item_returned')
            ->map(fn($i) => [
                'item_name' => $i->item_name,
                'quantity' => $i->quantity,
                'condition' => $i->remarks,
                'tenant_confirmed' => (bool) $i->tenant_confirmed,
                'is_returned' => (bool) $i->is_returned,
                'replacement_cost' => $i->replacement_cost,
            ])
            ->toArray();

        // Build financial data for the PDF
        $outstandingBalances = $this->buildOutstandingBalances($lease);
        $depositRefund = $lease->calculateDepositRefund();

        $data = [
            'tenant' => $this->currentTenant,
            'moveInChecklist' => $moveInChecklist,
            'moveOutChecklist' => $moveOutChecklist,
            'itemsReturned' => $itemsReturned,
            'outstandingBalances' => $outstandingBalances,
            'depositRefund' => $depositRefund,
            'tenantSignatureBase64' => 'data:image/png;base64,' . base64_encode(file_get_contents($tenantSigPath)),
            'ownerSignatureBase64'  => 'data:image/png;base64,' . base64_encode(file_get_contents($ownerSigPath)),
            'tenantSignedAt' => $lease->moveout_tenant_signed_at->format('M d, Y'),
            'ownerSignedAt'  => $lease->moveout_owner_signed_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('pdf.move-out-contract', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $pdfPath = "contracts/lease_{$lease->lease_id}_moveout_signed_" . time() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Delete old signed move-out PDF if exists
        if ($lease->moveout_signed_contract_path) {
            Storage::disk('public')->delete($lease->moveout_signed_contract_path);
        }

        $lease->update(['moveout_signed_contract_path' => $pdfPath]);
    }

    public function downloadMoveOutSignedContract()
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->moveout_signed_contract_path) return;

        return Storage::disk('public')->download(
            $lease->moveout_signed_contract_path,
            'Move-Out-Contract_' . ($this->currentTenant['personal_info']['first_name'] ?? '') . '-' . ($this->currentTenant['personal_info']['last_name'] ?? 'Tenant') . '_Unit-' . ($this->currentTenant['personal_info']['unit'] ?? 'N-A') . '.pdf'
        );
    }

    // ===== DISPUTE RESOLUTION (Manager side) =====

    public function resolveDispute(int $inspectionId, string $resolution, string $type = 'move_in', string $outcome = 'resolved'): void
    {
        $model = $type === 'move_out' ? MoveOutInspection::class : MoveInInspection::class;

        $item = $model::where('id', $inspectionId)
            ->where('lease_id', $this->currentLeaseId)
            ->where('dispute_status', 'disputed')
            ->first();

        if (!$item) return;

        $status = in_array($outcome, ['accepted', 'rejected']) ? "resolved_{$outcome}" : 'resolved';

        $item->update([
            'dispute_status' => $status,
            'resolution_remarks' => $resolution,
            'resolved_at' => now(),
        ]);

        ContractAuditLog::log($this->currentLeaseId, 'dispute_resolved', [
            'field_changed' => $item->item_name,
            'old_value' => $item->dispute_remarks,
            'new_value' => $resolution,
            'metadata' => [
                'inspection_type' => $type,
                'item_type' => $item->type,
            ],
        ]);

        // Notify tenant
        $lease = Lease::find($this->currentLeaseId);
        if ($lease) {
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'dispute_resolved',
                'title' => 'Dispute Resolved',
                'message' => 'Your dispute on "' . $item->item_name . '" has been resolved: ' . $resolution,
                'link' => '/tenant?tab=inspection',
            ]);
        }

        // Reload inspection data
        if ($type === 'move_in') {
            $this->loadInspectionData($lease);
        } else {
            $this->loadMoveOutInspectionData($lease);
        }

        $this->dispatch('notify', type: 'success', title: 'Dispute Resolved', description: 'The dispute has been resolved and the tenant has been notified.');
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-detail');
    }
}
