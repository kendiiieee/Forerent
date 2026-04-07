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
    public $managerSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignedAt = null;
    public $managerSignedAt = null;
    public $contractAgreed = false;

    // E-signature fields (move-out)
    public $showMoveOutSignatureModal = false;
    public $moveOutSignatureRole = '';
    public $moveOutTenantSignature = null;
    public $moveOutOwnerSignature = null;
    public $moveOutManagerSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutManagerSignedAt = null;
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

    // Deposit refund tracking
    public $depositInterestAmount = '';
    public $depositRefundReference = '';

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

        // Validate: damaged items must have repair costs entered (no TBD allowed)
        $costErrors = [];
        foreach ($this->moveOutChecklist as $index => $item) {
            $condition = $item['condition'] ?? '';
            $repairCost = $item['repair_cost'] ?? null;
            if (in_array($condition, ['damaged', 'missing']) && (empty($repairCost) || (float) $repairCost <= 0)) {
                $costErrors["moveOutChecklist.{$index}.repair_cost"] =
                    "Repair cost is required for \"{$item['item_name']}\" (condition: {$condition}).";
            }
        }
        foreach ($this->itemsReturned as $index => $item) {
            $isReturned = $item['is_returned'] ?? false;
            $qtyIssued = (int) ($item['quantity'] ?? 0);
            $qtyReturned = (int) ($item['quantity_returned'] ?? 0);
            $isPartial = $isReturned && $qtyIssued > 0 && $qtyReturned < $qtyIssued;
            $replacementCost = $item['replacement_cost'] ?? null;

            // Require replacement cost for fully unreturned OR partially returned items
            if ((!$isReturned || $isPartial) && (empty($replacementCost) || (float) $replacementCost <= 0)) {
                $label = $isPartial
                    ? "Replacement cost is required for partially returned \"{$item['item_name']}\" ({$qtyReturned}/{$qtyIssued} returned)."
                    : "Replacement cost is required for unreturned \"{$item['item_name']}\".";
                $costErrors["itemsReturned.{$index}.replacement_cost"] = $label;
            }

            // Validate quantity_returned doesn't exceed quantity issued
            if ($isReturned && $qtyIssued > 0 && $qtyReturned > $qtyIssued) {
                $costErrors["itemsReturned.{$index}.quantity_returned"] =
                    "Quantity returned ({$qtyReturned}) cannot exceed quantity issued ({$qtyIssued}).";
            }
        }
        if (!empty($costErrors)) {
            foreach ($costErrors as $key => $message) {
                $this->addError($key, $message);
            }
            $this->dispatch('scroll-to-error');
            $this->dispatch('notify', type: 'error', title: 'Missing Costs', description: 'Please enter repair/replacement costs for all damaged or unreturned items.');
            return;
        }

        $this->upsertInspection(
            $this->currentLeaseId, MoveOutInspection::class,
            $this->moveOutChecklist, $this->itemsReturned, 'item_returned'
        );

        // Mark all repair/replacement costs as confirmed
        MoveOutInspection::where('lease_id', $this->currentLeaseId)
            ->where(function ($q) {
                $q->whereNotNull('repair_cost')->where('repair_cost', '>', 0)
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('replacement_cost')->where('replacement_cost', '>', 0);
                  });
            })
            ->update(['repair_cost_confirmed' => true]);

        // Audit log
        ContractAuditLog::log($this->currentLeaseId, 'moveout_inspection_saved', [
            'metadata' => [
                'checklist_count' => count($this->moveOutChecklist),
                'items_count' => count($this->itemsReturned),
            ],
        ]);

        $lease = Lease::find($this->currentLeaseId);

        // If any signatures exist, reset them all since inspection data changed
        if ($lease && ($lease->moveout_tenant_signature || $lease->moveout_owner_signature || $lease->moveout_manager_signature)) {
            // Delete old signature files
            if ($lease->moveout_tenant_signature) {
                Storage::disk('public')->delete($lease->moveout_tenant_signature);
            }
            if ($lease->moveout_owner_signature) {
                Storage::disk('public')->delete($lease->moveout_owner_signature);
            }
            if ($lease->moveout_manager_signature) {
                Storage::disk('public')->delete($lease->moveout_manager_signature);
            }

            $lease->update([
                'moveout_tenant_signature' => null,
                'moveout_tenant_signed_at' => null,
                'moveout_tenant_signed_ip' => null,
                'moveout_owner_signature' => null,
                'moveout_owner_signed_at' => null,
                'moveout_owner_signed_ip' => null,
                'moveout_manager_signature' => null,
                'moveout_manager_signed_at' => null,
                'moveout_manager_signed_ip' => null,
                'moveout_contract_agreed' => false,
                'moveout_contract_status' => 'draft',
                'moveout_signed_contract_path' => null,
            ]);

            $this->moveOutTenantSignature = null;
            $this->moveOutOwnerSignature = null;
            $this->moveOutManagerSignature = null;
            $this->moveOutTenantSignedAt = null;
            $this->moveOutOwnerSignedAt = null;
            $this->moveOutManagerSignedAt = null;
            $this->moveOutContractAgreed = false;

            ContractAuditLog::log($this->currentLeaseId, 'moveout_signatures_reset', [
                'metadata' => ['reason' => 'Inspection data modified after signing'],
            ]);
        }

        // Auto-notify tenant that move-out inspection is ready (with cost summary)
        if ($lease) {
            $totalRepair = collect($this->moveOutChecklist)->sum(fn($i) => (float) ($i['repair_cost'] ?? 0));
            $totalReplacement = collect($this->itemsReturned)->filter(fn($i) => !($i['is_returned'] ?? false))->sum(fn($i) => (float) ($i['replacement_cost'] ?? 0));
            $costSummary = '';
            if ($totalRepair > 0) $costSummary .= ' Repair costs: PHP ' . number_format($totalRepair, 2) . '.';
            if ($totalReplacement > 0) $costSummary .= ' Replacement costs: PHP ' . number_format($totalReplacement, 2) . '.';

            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'inspection_ready',
                'title' => 'Move-Out Inspection Ready',
                'message' => 'Your move-out room inspection has been completed. Please review the assessed charges and confirm.' . $costSummary,
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
        $this->moveOutManagerSignature = null;
        $this->moveOutTenantSignedAt = null;
        $this->moveOutOwnerSignedAt = null;
        $this->moveOutManagerSignedAt = null;
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

        // Validate required fields for move-out initiation
        $moveOutErrors = [];
        if (empty(trim($this->forwardingAddress ?? ''))) {
            $moveOutErrors['forwardingAddress'] = 'Forwarding address is required for deposit refund correspondence.';
        }
        if (empty($this->reasonForVacating)) {
            $moveOutErrors['reasonForVacating'] = 'Reason for vacating is required.';
        }
        if (empty($this->depositRefundMethod)) {
            $moveOutErrors['depositRefundMethod'] = 'Refund method is required.';
        }
        if (empty(trim($this->depositRefundAccount ?? ''))) {
            $moveOutErrors['depositRefundAccount'] = 'Account name or number is required for refund processing.';
        }

        // Enforce: reason must match actual lease state
        if ($this->reasonForVacating && $lease->end_date) {
            $isBeforeEndDate = now()->lt($lease->end_date);
            $earlyReasons = [
                'Voluntary early termination by Lessee',
                'Mutual agreement between both parties',
                'Lease violation or termination by Lessor',
                'Transfer to a different unit / building (internal transfer)',
            ];
            $normalEndReason = 'End of lease term (contract expired)';

            if ($this->reasonForVacating === $normalEndReason && $isBeforeEndDate) {
                $moveOutErrors['reasonForVacating'] = 'Cannot select "End of lease term" — the lease has not expired yet (ends ' . $lease->end_date->format('M d, Y') . '). Please select the appropriate early termination reason.';
            }
            if (in_array($this->reasonForVacating, $earlyReasons) && !$isBeforeEndDate) {
                $moveOutErrors['reasonForVacating'] = 'The lease has already ended or is ending today. The correct reason is "End of lease term (contract expired)".';
            }
        }

        if (!empty($moveOutErrors)) {
            foreach ($moveOutErrors as $key => $message) {
                $this->addError($key, $message);
            }
            $this->dispatch('notify', type: 'error', title: 'Missing Information', description: 'Please fill in all required move-out details.');
            return;
        }

        $lease->update([
            'move_out_initiated_at' => now(),
            'forwarding_address' => $this->forwardingAddress,
            'reason_for_vacating' => $this->reasonForVacating,
            'deposit_refund_method' => $this->depositRefundMethod,
            'deposit_refund_account' => $this->depositRefundAccount,
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

        // Notify owner that their signature will be needed on the move-out contract
        $ownerId = $this->findOwnerIdForLease($lease);
        if ($ownerId) {
            $tenantName = $lease->tenant ? ($lease->tenant->first_name . ' ' . $lease->tenant->last_name) : 'a tenant';
            Notification::create([
                'user_id' => $ownerId,
                'type' => 'move_out_initiated',
                'title' => 'Move-Out Contract Signature Needed',
                'message' => "Move-out process has been initiated for {$tenantName}. Your signature on the move-out contract will be required after the inspection is completed. Please review and sign at your earliest convenience.",
                'link' => '/owner/property',
            ]);
        }

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

        // Check all costs are confirmed (no TBD values)
        $hasUnconfirmedCosts = MoveOutInspection::where('lease_id', $leaseId)
            ->where(function ($q) {
                // Damaged checklist items without repair cost
                $q->where(function ($q2) {
                    $q2->where('type', 'checklist')
                       ->whereIn('condition', ['damaged', 'missing'])
                       ->where(function ($q3) {
                           $q3->whereNull('repair_cost')->orWhere('repair_cost', 0);
                       });
                })
                // Unreturned items without replacement cost
                ->orWhere(function ($q2) {
                    $q2->where('type', 'item_returned')
                       ->where('is_returned', false)
                       ->where(function ($q3) {
                           $q3->whereNull('replacement_cost')->orWhere('replacement_cost', 0);
                       });
                });
            })
            ->exists();
        $costsConfirmed = $inspectionDone && !$hasUnconfirmedCosts;

        $lease = Lease::find($leaseId);
        $contractSigned = $lease
            && $lease->moveout_owner_signature
            && $lease->moveout_manager_signature
            && $lease->moveout_tenant_signature
            && $lease->moveout_contract_agreed;

        // 30-day notice enforcement for early termination (Contract Section 7)
        $isEarlyTermination = $lease
            && $lease->move_out_initiated_at
            && $lease->end_date
            && \Carbon\Carbon::parse($lease->move_out_initiated_at)->lt($lease->end_date);

        $noticePeriodMet = true;
        $noticeLabel = '30-day notice period (N/A — normal end of lease)';
        if ($isEarlyTermination) {
            $daysSinceNotice = \Carbon\Carbon::parse($lease->move_out_initiated_at)->diffInDays(\Carbon\Carbon::today());
            $noticePeriodMet = $daysSinceNotice >= 30;
            $noticeLabel = "30-day notice period elapsed ({$daysSinceNotice}/30 days)";
        }

        $this->moveOutPrerequisites = [
            ['label' => $unpaidCount === 0 ? 'All bills settled' : "Outstanding bills ({$unpaidCount}) — will be deducted from deposit", 'done' => true],
            ['label' => 'Move-out inspection completed', 'done' => $inspectionDone],
            ['label' => 'Items returned recorded', 'done' => $itemsReturnedDone],
            ['label' => 'All repair/replacement costs confirmed (no TBD)', 'done' => $costsConfirmed],
            ['label' => 'Move-out contract signed by both parties', 'done' => $contractSigned],
            ['label' => $noticeLabel, 'done' => $noticePeriodMet],
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

                // Auto-compute deposit interest (RA 9653 IRR §7b)
                $computedInterest = $lease->computeDepositInterest();
                $lease->update(['deposit_interest_amount' => $computedInterest]);

                // Auto-calculate deposit refund with original end_date
                $refundData = $lease->calculateDepositRefund($originalEndDate);
                $lease->update([
                    'deposit_refund_amount' => $refundData['refund_amount'],
                    'deposit_deductions' => $refundData['deductions'],
                    'deposit_refund_deadline' => $today->copy()->addDays(30),
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

    public function markRefundCompleted(): void
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease || $lease->status !== 'Expired' || $lease->deposit_refund_completed_at) return;

        $lease->update([
            'deposit_refund_completed_at' => now(),
            'deposit_refund_reference' => $this->depositRefundReference ?: null,
        ]);

        ContractAuditLog::log($lease->lease_id, 'deposit_refund_completed', [
            'metadata' => [
                'refund_amount' => $lease->deposit_refund_amount,
                'reference' => $this->depositRefundReference,
            ],
        ]);

        // Notify tenant
        if ($lease->tenant_id) {
            $amount = number_format((float) $lease->deposit_refund_amount, 2);
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'deposit_refund_completed',
                'title' => 'Deposit Refund Processed',
                'message' => "Your deposit refund of PHP {$amount} has been processed." .
                    ($this->depositRefundReference ? " Reference: {$this->depositRefundReference}" : ''),
                'link' => '/tenant?tab=inspection',
            ]);
        }

        $this->dispatch('notify',
            type: 'success',
            title: 'Refund Marked Complete',
            description: 'Tenant has been notified that the deposit refund has been processed.'
        );

        $this->depositRefundReference = '';
        $this->loadTenantData($this->currentTenantId);
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

    public function openSignatureModal(string $role = 'manager'): void
    {
        // Manager signs as witness
        if ($role !== 'manager') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        // Owner must sign first
        if (!$this->ownerSignature) {
            $this->dispatch('notify', type: 'warning', title: 'Owner Must Sign First', description: 'The property owner must sign the contract before the manager can sign as witness.');
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
        // Manager signs as witness
        if (!$this->currentLeaseId || $this->signatureRole !== 'manager') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, 'manager', 'movein');

        $this->managerSignature = $result['signature'];
        $this->managerSignedAt = $result['signedAt'];
        $this->contractAgreed = $result['agreed'];

        // Notify tenant that manager (witness) signed → tenant's turn
        $this->notifyTenantOfManagerSign($lease, 'move-in');

        // If all three signatures exist, generate PDF and auto-generate billing
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateSignedPdf($lease);
            $this->autoGenerateBillingOnExecution($lease);

            // Notify tenant that contract is fully executed
            Notification::create([
                'user_id' => $lease->tenant_id,
                'type' => 'contract_executed',
                'title' => 'Contract Fully Executed',
                'message' => 'Your move-in contract has been signed by all parties and is now active. You can download the signed copy from your dashboard.',
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
            'manager_signature'    => $lease->manager_signature,
            'manager_signed_at'    => $lease->manager_signed_at?->format('M d, Y h:i A'),
            'contract_agreed'      => (bool) $lease->contract_agreed,
            'signed_contract_path' => $lease->signed_contract_path,
        ];

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Witness Signature Saved', description: 'You have signed the move-in contract as witness.');
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

    /**
     * Resolve a file path from private (local) disk, falling back to public disk
     * for backward compatibility with existing files.
     */
    private function resolveSecureFilePath(?string $relativePath): ?string
    {
        if (!$relativePath) return null;
        if (Storage::disk('local')->exists($relativePath)) {
            return Storage::disk('local')->path($relativePath);
        }
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }
        return null;
    }

    private function generateSignedPdf(Lease $lease): void
    {
        $lease->load(['tenant', 'bed.unit.property']);

        // Verify all three signature files exist (check both private and public disks)
        $tenantSigPath  = $this->resolveSecureFilePath($lease->tenant_signature);
        $ownerSigPath   = $this->resolveSecureFilePath($lease->owner_signature);
        $managerSigPath = $this->resolveSecureFilePath($lease->manager_signature);

        if (!$tenantSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Tenant signature file is missing. Cannot generate signed contract PDF.');
            return;
        }
        if (!$ownerSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Owner signature file is missing. Cannot generate signed contract PDF.');
            return;
        }
        if (!$managerSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Manager witness signature file is missing. Cannot generate signed contract PDF.');
            return;
        }

        // Get manager name
        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        // Prepare additional data for PDF parity with web contract
        $property = $lease->bed?->unit?->property;
        $contractSettings = $property?->contract_settings ?? [];
        $dueDay = $this->currentTenant['move_in_details']['monthly_due_date'] ?? null;
        $dueSfx = match ((int) $dueDay) { 1, 21, 31 => 'st', 2, 22 => 'nd', 3, 23 => 'rd', default => 'th' };

        // Base64-encode government ID image for PDF appendix (check private then public)
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
            'tenant'                 => $this->currentTenant,
            'lessor'                 => $this->currentTenant['lessor_info'],
            't'                      => $this->currentTenant,
            'tenantSignatureBase64'  => 'data:image/png;base64,' . base64_encode(file_get_contents($tenantSigPath)),
            'ownerSignatureBase64'   => 'data:image/png;base64,' . base64_encode(file_get_contents($ownerSigPath)),
            'managerSignatureBase64' => 'data:image/png;base64,' . base64_encode(file_get_contents($managerSigPath)),
            'tenantSignedAt'         => $lease->tenant_signed_at->format('M d, Y'),
            'ownerSignedAt'          => $lease->owner_signed_at->format('M d, Y'),
            'managerSignedAt'        => $lease->manager_signed_at->format('M d, Y'),
            'managerName'            => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Unit Manager',
            'contractSettings'       => $contractSettings,
            'inspectionChecklist'    => $this->inspectionChecklist ?? [],
            'itemsReceived'          => $this->itemsReceived ?? [],
            'rate'                   => (float) ($this->currentTenant['move_in_details']['monthly_rate'] ?? 0),
            'deposit'                => (float) ($this->currentTenant['move_in_details']['security_deposit'] ?? 0),
            'premium'                => (float) ($this->currentTenant['move_in_details']['short_term_premium'] ?? 0),
            'dueDay'                 => $dueDay,
            'dueSfx'                 => $dueSfx,
            'govIdBase64'            => $govIdBase64,
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

    public function openMoveOutSignatureModal(string $role = 'manager'): void
    {
        // Manager signs as witness
        if ($role !== 'manager') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        // Owner must sign first
        if (!$this->moveOutOwnerSignature) {
            $this->dispatch('notify', type: 'warning', title: 'Owner Must Sign First', description: 'The property owner must sign the move-out contract before the manager can sign as witness.');
            return;
        }

        // Refresh outstanding balances to ensure real-time accuracy before signing
        $lease = Lease::find($this->currentLeaseId);
        if ($lease) {
            $this->currentTenant['outstanding_balances'] = $this->buildOutstandingBalances($lease);
            $this->currentTenant['deposit_refund'] = [
                'amount' => $lease->deposit_refund_amount,
                'deductions' => $lease->deposit_deductions,
                'interest_earned' => $lease->deposit_interest_amount,
            ];
        }

        // Block signing if there are TBD (unconfirmed) repair/replacement costs
        $hasTBD = MoveOutInspection::where('lease_id', $this->currentLeaseId)
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
        // Manager signs as witness
        if (!$this->currentLeaseId || $this->moveOutSignatureRole !== 'manager') return;

        if (!$this->authorizedForLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
            return;
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, 'manager', 'moveout');

        $this->moveOutManagerSignature = $result['signature'];
        $this->moveOutManagerSignedAt = $result['signedAt'];
        $this->moveOutContractAgreed = $result['agreed'];

        // Notify tenant that manager (witness) signed → tenant's turn
        $this->notifyTenantOfManagerSign($lease, 'move-out');

        // If all three signatures exist, generate PDF
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateMoveOutSignedPdf($lease);
        }

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');
        $this->dispatch('notify', type: 'success', title: 'Witness Signature Saved', description: 'You have signed the move-out contract as witness.');
    }

    private function generateMoveOutSignedPdf(Lease $lease): void
    {
        $lease->load(['tenant', 'bed.unit.property', 'moveInInspections', 'moveOutInspections']);

        // Verify all three signature files exist (check both private and public disks)
        $tenantSigPath  = $this->resolveSecureFilePath($lease->moveout_tenant_signature);
        $ownerSigPath   = $this->resolveSecureFilePath($lease->moveout_owner_signature);
        $managerSigPath = $this->resolveSecureFilePath($lease->moveout_manager_signature);

        if (!$tenantSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Tenant signature file is missing. Cannot generate signed move-out PDF.');
            return;
        }
        if (!$ownerSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Owner signature file is missing. Cannot generate signed move-out PDF.');
            return;
        }
        if (!$managerSigPath) {
            $this->dispatch('notify', type: 'error', title: 'PDF Error', description: 'Manager witness signature file is missing. Cannot generate signed move-out PDF.');
            return;
        }

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

        // Build items returned (include is_returned + quantity_returned + replacement_cost)
        $itemsReturned = $lease->moveOutInspections
            ->where('type', 'item_returned')
            ->map(fn($i) => [
                'item_name' => $i->item_name,
                'quantity' => $i->quantity,
                'quantity_returned' => $i->quantity_returned,
                'condition' => $i->remarks,
                'tenant_confirmed' => (bool) $i->tenant_confirmed,
                'is_returned' => (bool) $i->is_returned,
                'replacement_cost' => $i->replacement_cost,
            ])
            ->toArray();

        // Build financial data for the PDF
        $outstandingBalances = $this->buildOutstandingBalances($lease);
        $depositRefund = $lease->calculateDepositRefund();

        // Get manager name
        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        $data = [
            'tenant' => $this->currentTenant,
            'moveInChecklist' => $moveInChecklist,
            'moveOutChecklist' => $moveOutChecklist,
            'itemsReturned' => $itemsReturned,
            'outstandingBalances' => $outstandingBalances,
            'depositRefund' => $depositRefund,
            'tenantSignatureBase64'  => 'data:image/png;base64,' . base64_encode(file_get_contents($tenantSigPath)),
            'ownerSignatureBase64'   => 'data:image/png;base64,' . base64_encode(file_get_contents($ownerSigPath)),
            'managerSignatureBase64' => 'data:image/png;base64,' . base64_encode(file_get_contents($managerSigPath)),
            'tenantSignedAt'  => $lease->moveout_tenant_signed_at->format('M d, Y'),
            'ownerSignedAt'   => $lease->moveout_owner_signed_at->format('M d, Y'),
            'managerSignedAt' => $lease->moveout_manager_signed_at->format('M d, Y'),
            'managerName'     => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Unit Manager',
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
