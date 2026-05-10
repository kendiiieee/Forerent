<?php

namespace App\Livewire\Layouts\Tenants;

use App\Livewire\Concerns\CreatesMoveInBilling;
use App\Livewire\Concerns\InspectionConfig;
use App\Livewire\Concerns\SendsTenantWelcomeEmail;
use App\Livewire\Concerns\WithContractData;
use App\Livewire\Concerns\WithESignature;
use App\Livewire\Concerns\WithNotifications;
use App\Livewire\Concerns\WithPsgcAddress;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\ContractAuditLog;
use App\Models\Transaction;
use App\Models\Lease;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\PasswordGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;

class TenantDetail extends Component
{
    use WithESignature, WithContractData, WithNotifications, SendsTenantWelcomeEmail, CreatesMoveInBilling, WithPsgcAddress;

    public string $rejectionReason = '';

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
    public $reasonForVacating = '';
    public $depositRefundMethod = '';
    public $depositRefundAccount = '';

    // Early-vacate request form (manager initiates while a Notice of Termination is active)
    public $earlyVacateProposedDate = '';
    public $earlyVacateReason = '';

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
    public bool $moveOutLeaseExpired = false;

    // Deposit refund tracking
    public $depositInterestAmount = '';
    public $depositRefundReference = '';
    public ?array $moveOutRefundPreview = null;

    // Violations
    public $violations = [];
    public $violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];

    // Rental eligibility (reinstate flow — landlord only)
    public string $reinstateReason = '';

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

        // 'pending' tenants also have an Active lease (with approval_status='pending'),
        // so they need the Active-lease query — only 'transferred' / 'moved_out' look at Expired.
        if ($tab === 'current' || $tab === 'moving_out' || $tab === 'pending') {
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

        if ($this->isLeasePendingApproval()) {
            $this->dispatch('notify', type: 'warning', title: 'Awaiting Approval', description: 'You cannot run move-in inspection until the landlord approves this tenant.');
            return;
        }

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

    /**
     * Set the returned-state for an item via the segmented control.
     * Mode: 'all' (qty_returned = qty_issued), 'none' (= 0), or 'partial'
     * (start at 1, user adjusts via the inline qty input).
     */
    public function setReturnedState(int $index, string $mode): void
    {
        $qtyIssued = (int) ($this->itemsReturned[$index]['quantity'] ?? 0);
        $current   = (int) ($this->itemsReturned[$index]['quantity_returned'] ?? 0);

        $this->itemsReturned[$index]['quantity_returned'] = match ($mode) {
            'all'     => $qtyIssued,
            'none'    => 0,
            'partial' => ($current > 0 && $current < $qtyIssued) ? $current : max(1, $qtyIssued - 1),
            default   => $current,
        };

        // Clear any stale qty_returned validation since the value just changed
        $this->resetErrorBag("itemsReturned.{$index}.quantity_returned");
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

        if (!$this->inspectionSaved) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Move-In Inspection Required',
                description: 'Complete the move-in inspection before recording a move-out inspection.'
            );
            return;
        }

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
            $qtyIssued = (int) ($item['quantity'] ?? 0);
            $qtyReturned = (int) ($item['quantity_returned'] ?? 0);
            // Derive is_returned from qty_returned so the operator only fills one field.
            // Normalize on $this->itemsReturned so upsertInspection persists the right flag.
            $isReturned = $qtyReturned > 0;
            $this->itemsReturned[$index]['is_returned'] = $isReturned;
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
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        $this->loadViolations($lease);

        // A 3rd-offense violation may have just issued a Notice of Termination
        // on the lease — rebuild the full tenant array so the banner shows up
        // without the manager needing to refresh the page.
        if ($this->currentTenantId) {
            $this->loadTenant($this->currentTenantId, $this->viewingTab);
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
        $this->moveOutRefundPreview = null;
        $this->depositInterestAmount = '';
        $this->violations = [];
        $this->violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];
    }

    private function isManager(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    private function isLandlord(): bool
    {
        return Auth::user()?->role === 'landlord';
    }

    private function isLeasePendingApproval(): bool
    {
        if (!$this->currentLeaseId) return false;
        return Lease::where('lease_id', $this->currentLeaseId)
            ->where('approval_status', 'pending')
            ->exists();
    }

    /**
     * Verify the authenticated landlord owns the property tied to the current lease.
     */
    private function landlordOwnsCurrentLease(): bool
    {
        if (!$this->currentLeaseId || !$this->isLandlord()) return false;

        return Property::where('owner_id', Auth::id())
            ->whereHas('units.beds.leases', fn($q) => $q->where('lease_id', $this->currentLeaseId))
            ->exists();
    }

    public function approveTenant(): void
    {
        if (!$this->currentLeaseId) return;

        if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property landlord can approve a tenant.');
            return;
        }

        $lease = Lease::with('tenant', 'bed.unit')->find($this->currentLeaseId);
        if (!$lease || $lease->approval_status !== 'pending') {
            $this->dispatch('notify', type: 'warning', title: 'Already Processed', description: 'This tenant has already been reviewed.');
            return;
        }

        $tenant = $lease->tenant;
        if (!$tenant) {
            $this->dispatch('notify', type: 'error', title: 'Missing Tenant', description: 'Cannot approve — tenant record not found.');
            return;
        }

        // Detect transfer: tenant already has another approved+active lease at a
        // different bed. If so, this approval finalizes the bed move; otherwise
        // it's a brand-new tenant being approved.
        $previousLease = Lease::where('tenant_id', $tenant->user_id)
            ->where('lease_id', '!=', $lease->lease_id)
            ->where('status', 'Active')
            ->where('approval_status', 'approved')
            ->latest('lease_id')
            ->first();
        $isTransfer = (bool) $previousLease;

        $password   = $isTransfer ? null : PasswordGenerator::generate();
        $managerId  = $lease->bed?->unit?->manager_id;
        $tenantName = trim(($tenant->first_name ?? '') . ' ' . ($tenant->last_name ?? '')) ?: 'Tenant';

        DB::transaction(function () use ($lease, $tenant, $password, $previousLease, $isTransfer) {
            if ($isTransfer && $previousLease) {
                // Expire old lease and free old bed now that the transfer is approved.
                $previousLease->update([
                    'status'   => 'Expired',
                    'end_date' => Carbon::today(),
                ]);
                if ($previousLease->bed_id) {
                    Bed::where('bed_id', $previousLease->bed_id)->update(['status' => 'Vacant']);
                }
            } else {
                // New tenant: generate a fresh login password.
                $tenant->update(['password' => Hash::make($password)]);
            }

            $lease->update([
                'approval_status' => 'approved',
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
            ]);

            if ($isTransfer && $previousLease) {
                // Transfers don't charge advance + a fresh full deposit — the old deposit
                // carries over. Bill rent + premium + only the deposit shortfall (if any).
                $this->createTransferBilling($lease, $previousLease);
            } else {
                // New tenant: standard move-in billing (advance + deposit + premium).
                // Trait reads move_in_payment_method/move_in_or_number/move_in_receipt_image
                // off the lease for the Transaction record + audit log entry.
                $this->createMoveInBilling($lease, 'Paid');
            }

            // Notify tenant if government ID is missing
            if (!$tenant->government_id_type || !$tenant->government_id_number || !$tenant->government_id_image) {
                Notification::create([
                    'user_id' => $tenant->user_id,
                    'type'    => 'valid_id_required',
                    'title'   => 'Valid ID Required',
                    'message' => 'Please upload your valid government ID in Settings to complete your profile.',
                    'link'    => '/settings',
                ]);
            }

            ContractAuditLog::log($lease->lease_id, $isTransfer ? 'tenant_transfer_approved' : 'tenant_approved', [
                'metadata' => array_filter([
                    'approved_by'       => Auth::id(),
                    'previous_lease_id' => $previousLease?->lease_id,
                ]),
            ]);
        });

        if (!$isTransfer) {
            // Welcome email only for brand-new tenants — transferring tenants already have an account.
            $this->attemptWelcomeEmailDelivery($tenant->fresh(), $password);
        } else {
            // Tell the tenant their transfer went through.
            Notification::create([
                'user_id' => $tenant->user_id,
                'type'    => 'transfer_approved',
                'title'   => 'Bed Transfer Approved',
                'message' => 'Your bed transfer has been approved. Your new lease is now active.',
                'link'    => '/tenant',
            ]);
        }

        // Notify the manager who created the request
        if ($managerId) {
            Notification::create([
                'user_id' => $managerId,
                'type'    => $isTransfer ? 'tenant_transfer_approved' : 'tenant_approved',
                'title'   => $isTransfer ? 'Tenant Transfer Approved' : 'Tenant Approved',
                'message' => $isTransfer
                    ? "{$tenantName}'s bed transfer has been approved by the landlord. The previous bed is now vacant and move-in billing for the new lease has been generated."
                    : "{$tenantName} has been approved by the landlord. Move-in billing has been generated and the tenant has been emailed login details.",
                'link'    => '/manager/tenant',
            ]);
        }

        $this->loadTenant($this->currentTenantId, $this->viewingTab);
        $this->dispatch('refresh-tenant-list');
        $this->dispatch('notify',
            type: 'success',
            title: $isTransfer ? 'Transfer Approved' : 'Tenant Approved',
            description: $isTransfer
                ? 'The transfer is complete. The tenant has been notified.'
                : 'The tenant has been approved and notified.'
        );
    }

    public function openRejectModal(): void
    {
        if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) return;
        $this->rejectionReason = '';
        $this->dispatch('open-modal', 'reject-tenant-confirmation');
    }

    public function rejectTenant(): void
    {
        if (!$this->currentLeaseId) return;

        if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
            $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property landlord can reject a tenant.');
            return;
        }

        $reason = trim($this->rejectionReason);
        if ($reason === '') {
            $this->addError('rejectionReason', 'Please provide a reason for rejecting this tenant.');
            return;
        }

        $lease = Lease::with('tenant', 'bed.unit')->find($this->currentLeaseId);
        if (!$lease || $lease->approval_status !== 'pending') {
            $this->dispatch('notify', type: 'warning', title: 'Already Processed', description: 'This tenant has already been reviewed.');
            return;
        }

        $managerId = $lease->bed?->unit?->manager_id;
        $tenantName = $lease->tenant ? trim($lease->tenant->first_name . ' ' . $lease->tenant->last_name) : 'Tenant';

        // Detect transfer rejection — the tenant has another approved+active lease
        // that should be left untouched. Without this, rejecting just discards the
        // pending lease (which is the right behavior either way).
        $tenantId = $lease->tenant_id;
        $isTransfer = $tenantId && Lease::where('tenant_id', $tenantId)
            ->where('lease_id', '!=', $lease->lease_id)
            ->where('status', 'Active')
            ->where('approval_status', 'approved')
            ->exists();

        DB::transaction(function () use ($lease, $reason, $isTransfer) {
            $lease->update([
                'approval_status'   => 'rejected',
                'status'            => 'Expired',
                'rejection_reason'  => $reason,
                'approved_by'       => Auth::id(),
                'approved_at'       => now(),
                'end_date'          => Carbon::today(),
            ]);

            if ($lease->bed_id) {
                Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Vacant']);
            }

            ContractAuditLog::log($lease->lease_id, $isTransfer ? 'tenant_transfer_rejected' : 'tenant_rejected', [
                'metadata' => ['rejected_by' => Auth::id(), 'reason' => $reason],
            ]);
        });

        if ($managerId) {
            Notification::create([
                'user_id' => $managerId,
                'type'    => $isTransfer ? 'tenant_transfer_rejected' : 'tenant_rejected',
                'title'   => $isTransfer ? 'Tenant Transfer Rejected' : 'Tenant Rejected',
                'message' => $isTransfer
                    ? "{$tenantName}'s bed transfer was rejected by the landlord. The tenant remains on their current bed. Reason: {$reason}"
                    : "{$tenantName} was rejected by the landlord. Reason: {$reason}",
                'link'    => '/manager/tenant',
            ]);
        }

        if ($isTransfer && $tenantId) {
            Notification::create([
                'user_id' => $tenantId,
                'type'    => 'transfer_rejected',
                'title'   => 'Bed Transfer Rejected',
                'message' => "Your bed transfer was rejected by the landlord. You will remain on your current bed. Reason: {$reason}",
                'link'    => '/tenant',
            ]);
        }

        $this->rejectionReason = '';
        $this->dispatch('close-modal', 'reject-tenant-confirmation');
        $this->resetTenantData();
        $this->dispatch('refresh-tenant-list');
        $this->dispatch('notify',
            type: 'success',
            title: $isTransfer ? 'Transfer Rejected' : 'Tenant Rejected',
            description: $isTransfer
                ? 'The transfer was rejected. The tenant remains on their current bed.'
                : 'The application has been rejected and the bed is vacant again.'
        );
    }

    public function editTenant(): void
    {
        if ($this->isLandlord()) return;
        if ($this->currentTenantId) {
            $this->dispatch('open-edit-tenant-modal', tenantId: $this->currentTenantId);
        }
    }

    public function transferTenant(): void
    {
        if ($this->isLandlord()) return;
        if (!$this->currentTenantId || !$this->currentLeaseId) return;

        $lease = Lease::with('moveInInspections')->find($this->currentLeaseId);
        if (!$lease) return;

        $hasInspection = $lease->moveInInspections->isNotEmpty();
        $contractExecuted = $lease->contract_status === 'executed';

        if (!$hasInspection || !$contractExecuted) {
            $missing = [];
            if (!$hasInspection)     $missing[] = 'move-in inspection';
            if (!$contractExecuted)  $missing[] = 'move-in contract signing';

            $this->notifyWarning(
                'Complete Move-In First',
                'Finish the ' . implode(' and ', $missing) . ' for this tenant before transferring them to another bed.'
            );
            return;
        }

        $this->dispatch('open-transfer-tenant-modal', tenantId: $this->currentTenantId);
    }

    public function moveOutTenant(): void
    {
        if ($this->isLandlord()) return;
        if (!$this->currentTenantId) return;

        // If already initiated, open the confirmation modal
        if ($this->moveOutInitiated) {
            $this->computeMoveOutPrerequisites();
            $this->dispatch('open-modal', 'move-out-confirmation');
            return;
        }

        // Termination notice gate — if a notice is on file, the tenant is entitled
        // to the full notice period. Block initiation until either:
        //   (a) the vacate-by date has arrived, or
        //   (b) an early-vacate request is on file AND the tenant has accepted it.
        $leaseForGate = $this->currentLeaseId ? Lease::find($this->currentLeaseId) : null;
        if ($leaseForGate?->termination_notice_issued_at && $leaseForGate->vacate_by_date) {
            $vacateBy = $leaseForGate->vacate_by_date->startOfDay();
            $hasAcceptedEarlyVacate = $leaseForGate->early_vacate_status === 'accepted';
            if (now()->startOfDay()->lt($vacateBy) && !$hasAcceptedEarlyVacate) {
                $this->notifyWarning(
                    'Notice Period Still Active',
                    'Tenant is entitled to the full notice period. Move-out can be initiated on or after ' . $vacateBy->format('M d, Y') . ', or earlier if the tenant accepts an early-vacate request.'
                );
                return;
            }
        }

        // Auto-derive reason from lease state. Precedence:
        //   1. Termination notice on file → reason = lease violation
        //   2. End date passed → reason = end of lease term
        //   3. Otherwise → blank, manager picks
        $lease = $this->currentLeaseId ? Lease::find($this->currentLeaseId) : null;
        $this->moveOutLeaseExpired = (bool) ($lease?->end_date && now()->gte($lease->end_date));
        if ($lease?->termination_notice_issued_at) {
            $this->reasonForVacating = 'Lease violation or termination by Lessor';
        } elseif ($this->moveOutLeaseExpired) {
            $this->reasonForVacating = 'End of lease term (contract expired)';
        } else {
            $this->reasonForVacating = '';
        }
        $this->resetErrorBag('reasonForVacating');

        $this->dispatch('open-modal', 'initiate-move-out');
    }

    /**
     * Open the modal for the manager to file an early-vacate request.
     * Defaults the proposed date to today; tenant must still accept before
     * the move-out gate unlocks.
     */
    public function openEarlyVacateModal(): void
    {
        if ($this->isLandlord()) return;
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->termination_notice_issued_at) return;

        $this->earlyVacateProposedDate = now()->toDateString();
        $this->earlyVacateReason = '';
        $this->resetErrorBag(['earlyVacateProposedDate', 'earlyVacateReason']);
        $this->dispatch('open-modal', 'request-early-vacate');
    }

    /**
     * Manager files an early-vacate request. Writes the proposed date + reason
     * to the lease, sets status=pending_tenant, and notifies the tenant.
     * The tenant must accept (or decline) before the move-out gate unlocks.
     */
    public function requestEarlyVacate(): void
    {
        if ($this->isLandlord()) return;
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->termination_notice_issued_at) return;

        $errors = [];
        if (empty($this->earlyVacateProposedDate)) {
            $errors['earlyVacateProposedDate'] = 'Proposed early vacate date is required.';
        } else {
            $proposed = \Carbon\Carbon::parse($this->earlyVacateProposedDate)->startOfDay();
            if ($proposed->lt(now()->startOfDay())) {
                $errors['earlyVacateProposedDate'] = 'Proposed date cannot be in the past.';
            } elseif ($proposed->gte($lease->vacate_by_date->startOfDay())) {
                $errors['earlyVacateProposedDate'] = 'Proposed date must be earlier than the original vacate-by date (' . $lease->vacate_by_date->format('M d, Y') . ').';
            }
        }
        if (empty(trim($this->earlyVacateReason ?? '')) || strlen(trim($this->earlyVacateReason)) < 10) {
            $errors['earlyVacateReason'] = 'Please provide a reason / agreement details (at least 10 characters).';
        }

        if (!empty($errors)) {
            foreach ($errors as $key => $msg) $this->addError($key, $msg);
            return;
        }

        $lease->update([
            'early_vacate_requested_at'   => now(),
            'early_vacate_proposed_date'  => $this->earlyVacateProposedDate,
            'early_vacate_request_reason' => trim($this->earlyVacateReason),
            'early_vacate_status'         => 'pending_tenant',
            'early_vacate_requested_by'   => Auth::id(),
            'early_vacate_responded_at'   => null,
            'early_vacate_response_note'  => null,
        ]);

        ContractAuditLog::log($lease->lease_id, 'early_vacate_requested', [
            'metadata' => [
                'proposed_date' => $this->earlyVacateProposedDate,
                'reason'        => trim($this->earlyVacateReason),
                'requested_by'  => Auth::id(),
            ],
        ]);

        Notification::create([
            'user_id' => $lease->tenant_id,
            'type'    => 'early_vacate_requested',
            'title'   => 'Early Vacate Request — Action Required',
            'message' => 'Management has proposed moving up your vacate date to ' . \Carbon\Carbon::parse($this->earlyVacateProposedDate)->format('M d, Y') . '. Please review and accept or decline on your dashboard.',
            'link'    => '/tenant',
        ]);

        $this->dispatch('close-modal', 'request-early-vacate');
        $this->notifySuccess('Early Vacate Request Sent', 'Awaiting tenant response.');
        $this->loadTenant($this->currentTenantId, $this->viewingTab);
    }

    /**
     * Manager withdraws an outstanding early-vacate request (e.g. tenant
     * changed their mind, or the request was filed in error).
     */
    public function cancelEarlyVacateRequest(): void
    {
        if ($this->isLandlord()) return;
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->early_vacate_status) return;

        // Don't allow cancellation after the tenant accepted — at that point
        // the move-out flow may already be in motion.
        if ($lease->early_vacate_status === 'accepted') {
            $this->notifyWarning('Cannot Cancel', 'The tenant has already accepted this request.');
            return;
        }

        $previousStatus = $lease->early_vacate_status;
        $lease->update([
            'early_vacate_requested_at'   => null,
            'early_vacate_proposed_date'  => null,
            'early_vacate_request_reason' => null,
            'early_vacate_status'         => null,
            'early_vacate_requested_by'   => null,
            'early_vacate_responded_at'   => null,
            'early_vacate_response_note'  => null,
        ]);

        ContractAuditLog::log($lease->lease_id, 'early_vacate_request_cancelled', [
            'metadata' => ['previous_status' => $previousStatus, 'cancelled_by' => Auth::id()],
        ]);

        $this->notifySuccess('Request Cancelled', 'The early-vacate request was withdrawn.');
        $this->loadTenant($this->currentTenantId, $this->viewingTab);
    }

    public function initiateMoveOut(): void
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease || $lease->move_out_initiated_at) return;

        // Validate required fields for move-out initiation
        $moveOutErrors = [];
        if (empty($this->reasonForVacating)) {
            $moveOutErrors['reasonForVacating'] = 'Reason for vacating is required.';
        }
        if (empty($this->depositRefundMethod)) {
            $moveOutErrors['depositRefundMethod'] = 'Refund method is required.';
        }
        if ($this->depositRefundMethod !== 'Cash' && empty(trim($this->depositRefundAccount ?? ''))) {
            $moveOutErrors['depositRefundAccount'] = 'Account name or number is required for refund processing.';
        }

        // Enforce: reason must match actual lease state
        if ($this->reasonForVacating && $lease->end_date) {
            $isBeforeEndDate = now()->lt($lease->end_date);
            $earlyReasons = [
                'Voluntary early termination by Lessee',
                'Mutual agreement between both parties',
                'Lease violation or termination by Lessor',
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
            'reason_for_vacating' => $this->reasonForVacating,
            'deposit_refund_method' => $this->depositRefundMethod,
            'deposit_refund_account' => $this->depositRefundMethod === 'Cash' ? null : $this->depositRefundAccount,
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
        // Tenant just moved Current → Moving Out — refresh the navigation list/counts.
        $this->dispatch('refresh-tenant-list');
        $this->dispatch('notify', type: 'success', title: 'Move-Out Initiated', description: 'The move-out process has been started. You can now complete the inspection and contract.');
    }

    public function saveMoveOutDetails(): void
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $lease->update([
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
            // startOfDay + abs + int — Carbon 3 returns signed floats, and the
            // initiated_at timestamp is mid-day while today() is midnight.
            $daysSinceNotice = (int) abs(
                \Carbon\Carbon::parse($lease->move_out_initiated_at)
                    ->startOfDay()
                    ->diffInDays(\Carbon\Carbon::today()->startOfDay())
            );
            $noticePeriodMet = $daysSinceNotice >= 30;
            $noticeLabel = "30-day notice period elapsed ({$daysSinceNotice}/30 days)";
        }

        $this->moveOutPrerequisites = [
            [
                'label' => $unpaidCount === 0
                    ? 'All bills settled'
                    : "Outstanding bills ({$unpaidCount}) — must be paid before finalize, or use Forfeit Deposit",
                'done'  => $unpaidCount === 0,
            ],
            ['label' => 'Move-out inspection completed', 'done' => $inspectionDone],
            ['label' => 'Items returned recorded', 'done' => $itemsReturnedDone],
            ['label' => 'All repair/replacement costs confirmed (no TBD)', 'done' => $costsConfirmed],
            ['label' => 'Move-out contract signed by both parties', 'done' => $contractSigned],
            ['label' => $noticeLabel, 'done' => $noticePeriodMet],
        ];

        $this->computeMoveOutRefundPreview($lease);
    }

    /**
     * Build a non-persistent refund preview so the modal can show what the
     * tenant will actually receive (or whether the deposit is forfeited).
     */
    public function computeMoveOutRefundPreview(?Lease $lease = null): void
    {
        $lease = $lease ?? ($this->currentLeaseId ? Lease::find($this->currentLeaseId) : null);
        if (!$lease) {
            $this->moveOutRefundPreview = null;
            return;
        }

        $originalEndDate = $lease->end_date;

        // Simulate what confirmMoveOut() will do, without persisting.
        $lease->move_out = \Carbon\Carbon::today();

        $manual = trim((string) $this->depositInterestAmount);
        $lease->deposit_interest_amount = ($manual !== '' && is_numeric($manual))
            ? (float) $manual
            : null;

        $this->moveOutRefundPreview = $lease->calculateDepositRefund($originalEndDate);
    }

    public function updatedDepositInterestAmount(): void
    {
        $this->computeMoveOutRefundPreview();
    }

    public function confirmMoveOut(): void
    {
        $this->finalizeMoveOutFlow(skipUnpaidGate: false);
    }

    /**
     * Manager explicitly forfeits the deposit and blocks the tenant when there
     * are unpaid bills they cannot collect (e.g. tenant abandoned the unit).
     * Bypasses ONLY the outstanding-bills gate — other prerequisites still apply.
     */
    public function forfeitAndFinalizeMoveOut(): void
    {
        $this->finalizeMoveOutFlow(skipUnpaidGate: true);
    }

    private function finalizeMoveOutFlow(bool $skipUnpaidGate): void
    {
        if (!$this->currentTenantId) return;

        $activeLeases = Lease::where('tenant_id', $this->currentTenantId)
            ->where('status', 'Active')
            ->get(['lease_id', 'bed_id', 'end_date']);

        if ($activeLeases->isEmpty()) {
            $this->dispatch('close-modal', 'move-out-confirmation');
            $this->dispatch('close-modal', 'forfeit-deposit-confirmation');
            $this->dispatch('notify',
                type: 'warning',
                title: 'No Active Lease',
                description: 'This tenant has no active lease to move out.'
            );
            return;
        }

        // Check all prerequisites at once. Forfeit flow skips ONLY the unpaid-bills gate.
        $this->computeMoveOutPrerequisites();
        $blockers = collect($this->moveOutPrerequisites)->filter(fn($p) => !$p['done']);
        if ($skipUnpaidGate) {
            $blockers = $blockers->reject(fn($p) => str_contains($p['label'], 'Outstanding bills'));
        }

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

                // Unpaid balance at move-out (excludes deposit refund, which is computed separately)
                $unpaidTotal = (float) $lease->billings()
                    ->whereIn('status', ['Unpaid', 'Overdue'])
                    ->sum('amount');

                $isEarly = $originalEndDate && $today->lt($originalEndDate);
                $terminationReason = match (true) {
                    $unpaidTotal > 0 => 'non_payment',
                    $isEarly         => 'early_termination',
                    default          => 'normal_expiry',
                };

                $lease->update([
                    'status'              => 'Expired',
                    'move_out'            => $today,
                    'end_date'            => $today,
                    'termination_reason'  => $terminationReason,
                ]);

                // Deposit interest (RA 9653 IRR §7b) — manual entry overrides auto-computed.
                // Early termination forfeits the entire deposit including any interest, per
                // Contract Section 7, so we save 0 to keep the audit record consistent.
                if ($terminationReason === 'early_termination') {
                    $interest = 0.0;
                } else {
                    $manual = trim((string) $this->depositInterestAmount);
                    $interest = ($manual !== '' && is_numeric($manual))
                        ? (float) $manual
                        : $lease->computeDepositInterest();
                }
                $lease->update(['deposit_interest_amount' => $interest]);

                // Auto-calculate deposit refund with original end_date
                $refundData = $lease->calculateDepositRefund($originalEndDate);
                $lease->update([
                    'deposit_refund_amount' => $refundData['refund_amount'],
                    'deposit_deductions' => $refundData['deductions'],
                    'deposit_refund_deadline' => $today->copy()->addDays(30),
                ]);

                // Hard-block tenant from renting again if they left with an unpaid balance
                if ($terminationReason === 'non_payment' && $lease->tenant) {
                    $lease->tenant->block(
                        'Lease #' . $lease->lease_id . ' ended with outstanding balance of PHP '
                            . number_format($unpaidTotal, 2) . ' deducted from the security deposit.',
                        $lease->lease_id,
                        Auth::id()
                    );
                }

                ContractAuditLog::log($lease->lease_id, 'move_out_completed', [
                    'metadata' => [
                        'deposit_refund' => $refundData['refund_amount'],
                        'total_deductions' => $refundData['total_deductions'],
                        'deductions' => $refundData['deductions'],
                        'original_end_date' => $originalEndDate?->format('Y-m-d'),
                        'termination_reason' => $terminationReason,
                        'unpaid_total' => $unpaidTotal,
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
        $this->dispatch('close-modal', 'forfeit-deposit-confirmation');
        $this->resetTenantData();
        $this->dispatch('notify',
            type: 'success',
            title: $skipUnpaidGate ? 'Move-Out Finalized — Deposit Forfeited' : 'Tenant Moved Out',
            description: $skipUnpaidGate
                ? 'Lease expired, unpaid bills deducted from deposit, and the tenant has been blocked from new rentals.'
                : 'Lease marked as expired, deposit refund calculated, and bed status updated.'
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

    public function reinstateTenant(): void
    {
        if (!$this->currentTenantId) return;

        $actor = Auth::user();
        if (!$actor || $actor->role !== 'landlord') {
            $this->dispatch('notify',
                type: 'error',
                title: 'Not Allowed',
                description: 'Only the landlord can reinstate a blocked tenant.'
            );
            return;
        }

        $reason = trim($this->reinstateReason);
        if ($reason === '') {
            $this->addError('reinstateReason', 'Please provide a reason for reinstating this tenant.');
            return;
        }

        $tenant = User::find($this->currentTenantId);
        if (!$tenant || !$tenant->isBlockedFromRenting()) {
            $this->dispatch('close-modal', 'reinstate-tenant-confirmation');
            return;
        }

        $tenant->reinstate($reason, $actor);

        $this->reinstateReason = '';
        $this->dispatch('close-modal', 'reinstate-tenant-confirmation');
        $this->loadTenant($this->currentTenantId, $this->viewingTab);
        $this->dispatch('notify',
            type: 'success',
            title: 'Tenant Reinstated',
            description: 'This tenant is now eligible to rent again.'
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
     * Re-pull the lease's signature state from DB so other parties' signatures
     * appear without requiring a page reload. Wired up via wire:poll inside the
     * contract modals so the cost is paid only while a modal is open.
     */
    public function refreshContractSignatures(): void
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $this->loadSignatureState($lease);

        if (is_array($this->currentTenant)) {
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
            $this->currentTenant['contract_status'] = $lease->contract_status;
        }
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

    public function openSignatureModal(string $role = ''): void
    {
        // Default to the role implied by the current user when called without args.
        if (!in_array($role, ['owner', 'manager'], true)) {
            $role = $this->isLandlord() ? 'owner' : 'manager';
        }

        if ($this->isLeasePendingApproval()) {
            $this->dispatch('notify', type: 'warning', title: 'Awaiting Approval', description: 'Cannot sign contract until the landlord approves this tenant.');
            return;
        }

        if ($role === 'owner') {
            if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign as lessor.');
                return;
            }
        } else {
            if (!$this->authorizedForLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
                return;
            }
            // Owner must sign first
            if (!$this->ownerSignature) {
                $this->dispatch('notify', type: 'warning', title: 'Owner Must Sign First', description: 'The property owner must sign the contract before the manager can sign as witness.');
                return;
            }
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
        if (!$this->currentLeaseId || !in_array($this->signatureRole, ['owner', 'manager'], true)) return;

        $role = $this->signatureRole;

        if ($role === 'owner') {
            if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign as lessor.');
                return;
            }
        } else {
            if (!$this->authorizedForLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
                return;
            }
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, $role, 'movein');

        if ($role === 'owner') {
            $this->ownerSignature = $result['signature'];
            $this->ownerSignedAt  = $result['signedAt'];
            // Notify manager next-in-line + tenant of progress
            $this->notifyManagerOfOwnerSign($lease, 'move-in');
            $this->notifyTenantOfOwnerSignProgress($lease, 'move-in');
        } else {
            $this->managerSignature = $result['signature'];
            $this->managerSignedAt  = $result['signedAt'];
            // Notify tenant next-in-line + owner of progress
            $this->notifyTenantOfManagerSign($lease, 'move-in');
            $this->notifyOwnerOfManagerSignProgress($lease, 'move-in');
        }

        $this->contractAgreed = $result['agreed'];

        // If all three signatures exist, generate PDF, billing, and notify everyone
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateSignedPdf($lease);
            $this->autoGenerateBillingOnExecution($lease);
            $this->notifyAllOfContractExecuted($lease, 'move-in');
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

        $title = $role === 'owner' ? 'Owner Signature Saved' : 'Witness Signature Saved';
        $desc  = $role === 'owner'
            ? 'You have signed the move-in contract as the property owner.'
            : 'You have signed the move-in contract as witness.';
        $this->dispatch('notify', type: 'success', title: $title, description: $desc);
    }

    /**
     * Create the first billing when a transfer is approved by the landlord.
     * Differs from createMoveInBilling: charges rent + premium + only the deposit
     * shortfall (since the old deposit carries over from the previous lease).
     */
    private function createTransferBilling(Lease $lease, Lease $previousLease): void
    {
        if ($lease->billings()->exists()) return;

        $rate = (float) $lease->contract_rate;
        $premium = (float) ($lease->short_term_premium ?? 0);
        $newDeposit = (float) $lease->security_deposit;
        $oldDeposit = (float) ($previousLease->security_deposit ?? 0);
        $depositShortfall = max(0, $newDeposit - $oldDeposit);

        $startDate = Carbon::parse($lease->start_date ?? now());
        $totalCharges = $rate + $premium + $depositShortfall;

        $billing = Billing::create([
            'lease_id'     => $lease->lease_id,
            'billing_type' => 'monthly',
            'billing_date' => $startDate->format('Y-m-d'),
            'next_billing' => $startDate->copy()->addMonth()->format('Y-m-d'),
            'due_date'     => $startDate->copy()->addDays(5)->format('Y-m-d'),
            'to_pay'       => $totalCharges,
            'amount'       => $totalCharges,
            'status'       => 'Paid',
        ]);

        BillingItem::create([
            'billing_id'      => $billing->billing_id,
            'charge_category' => 'recurring',
            'charge_type'     => 'rent',
            'description'     => 'Monthly Rent',
            'amount'          => $rate,
        ]);

        if ($premium > 0) {
            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'conditional',
                'charge_type'     => 'short_term_premium',
                'description'     => 'Short-Term Premium (contract under 6 months)',
                'amount'          => $premium,
            ]);
        }

        if ($depositShortfall > 0) {
            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'conditional',
                'charge_type'     => 'security_deposit',
                'description'     => 'Security Deposit Top-Up (transfer)',
                'amount'          => $depositShortfall,
            ]);
        }

        $txn = Transaction::create([
            'billing_id'       => $billing->billing_id,
            'reference_number' => 'placeholder',
            'or_number'        => $lease->move_in_or_number,
            'transaction_type' => 'Debit',
            'category'         => 'Rent Payment',
            'payment_method'   => $lease->move_in_payment_method ?: 'Cash',
            'transaction_date' => today(),
            'amount'           => $totalCharges,
        ]);
        $txn->update([
            'reference_number' => 'TRF-' . now()->format('Ymd') . '-'
                . str_pad((string) $txn->transaction_id, 6, '0', STR_PAD_LEFT),
        ]);

        ContractAuditLog::log($lease->lease_id, 'transfer_payment_recorded', [
            'metadata' => [
                'amount'             => $totalCharges,
                'payment_method'     => $lease->move_in_payment_method,
                'or_number'          => $lease->move_in_or_number,
                'receipt_image'      => $lease->move_in_receipt_image,
                'reference'          => $txn->reference_number,
                'previous_lease_id'  => $previousLease->lease_id,
                'previous_deposit'   => $oldDeposit,
                'deposit_shortfall'  => $depositShortfall,
                'recorded_by'        => Auth::id(),
            ],
        ]);
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
            ->setOption('isRemoteEnabled', false);

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

        $lease = Lease::with(['tenant', 'bed.unit.property'])->find($this->currentLeaseId);
        if (!$lease) return;

        $tenant = $lease->tenant;
        $unitNumber = $lease->bed->unit->unit_number ?? 'N-A';
        $filename = 'Move-In-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . $unitNumber . '.pdf';

        if ($lease->signed_contract_path && Storage::disk('public')->exists($lease->signed_contract_path)) {
            return Storage::disk('public')->download($lease->signed_contract_path, $filename);
        }

        $t = $this->currentTenant;
        $rate = (float) ($t['move_in_details']['monthly_rate'] ?? 0);
        $deposit = (float) ($t['move_in_details']['security_deposit'] ?? 0);
        $premium = (float) ($t['move_in_details']['short_term_premium'] ?? 0);
        $dueDay = $t['move_in_details']['monthly_due_date'] ?? null;
        $dueSfx = match ((int) $dueDay) { 1, 21, 31 => 'st', 2, 22 => 'nd', 3, 23 => 'rd', default => 'th' };

        $property = $lease->bed?->unit?->property;
        $contractSettings = $property?->contract_settings ?? [];

        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        $govIdImage = $tenant->government_id_image;
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
            ->setOption('isRemoteEnabled', false);

        $cachePath = 'contracts/move-in-' . $lease->id . '.pdf';
        Storage::disk('public')->put($cachePath, $pdf->output());
        $lease->update(['signed_contract_path' => $cachePath]);

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

    public function openMoveOutSignatureModal(string $role = ''): void
    {
        // Default to the role implied by the current user when called without args.
        if (!in_array($role, ['owner', 'manager'], true)) {
            $role = $this->isLandlord() ? 'owner' : 'manager';
        }

        if ($role === 'owner') {
            if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign as lessor.');
                return;
            }
        } else {
            if (!$this->authorizedForLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
                return;
            }

            // Owner must sign first
            if (!$this->moveOutOwnerSignature) {
                $this->dispatch('notify', type: 'warning', title: 'Owner Must Sign First', description: 'The property owner must sign the move-out contract before the manager can sign as witness.');
                return;
            }
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
        if (!$this->currentLeaseId || !in_array($this->moveOutSignatureRole, ['owner', 'manager'], true)) return;

        $role = $this->moveOutSignatureRole;

        if ($role === 'owner') {
            if (!$this->isLandlord() || !$this->landlordOwnsCurrentLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'Only the property owner can sign as lessor.');
                return;
            }
        } else {
            if (!$this->authorizedForLease()) {
                $this->dispatch('notify', type: 'error', title: 'Unauthorized', description: 'You are not authorized to sign this contract.');
                return;
            }
        }

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, $role, 'moveout');

        if ($role === 'owner') {
            $this->moveOutOwnerSignature = $result['signature'];
            $this->moveOutOwnerSignedAt  = $result['signedAt'];
            $this->notifyManagerOfOwnerSign($lease, 'move-out');
            $this->notifyTenantOfOwnerSignProgress($lease, 'move-out');
        } else {
            $this->moveOutManagerSignature = $result['signature'];
            $this->moveOutManagerSignedAt  = $result['signedAt'];
            $this->notifyTenantOfManagerSign($lease, 'move-out');
            $this->notifyOwnerOfManagerSignProgress($lease, 'move-out');
        }

        $this->moveOutContractAgreed = $result['agreed'];

        // If all three signatures exist, generate PDF and notify everyone
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateMoveOutSignedPdf($lease);
            $this->notifyAllOfContractExecuted($lease, 'move-out');
        }

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');

        $title = $role === 'owner' ? 'Owner Signature Saved' : 'Witness Signature Saved';
        $desc  = $role === 'owner'
            ? 'You have signed the move-out contract as the property owner.'
            : 'You have signed the move-out contract as witness.';
        $this->dispatch('notify', type: 'success', title: $title, description: $desc);
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
            ->setOption('isRemoteEnabled', false);

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

        $lease = Lease::with(['tenant', 'bed.unit.property'])->find($this->currentLeaseId);
        if (!$lease) return;

        $tenant = $lease->tenant;
        $unitNumber = $lease->bed->unit->unit_number ?? 'N-A';
        $filename = 'Move-Out-Contract_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . $unitNumber . '.pdf';

        if ($lease->moveout_signed_contract_path && Storage::disk('public')->exists($lease->moveout_signed_contract_path)) {
            return Storage::disk('public')->download($lease->moveout_signed_contract_path, $filename);
        }

        $t = $this->currentTenant;
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
            ->setOption('isRemoteEnabled', false);

        $cachePath = 'contracts/move-out-' . $lease->id . '.pdf';
        Storage::disk('public')->put($cachePath, $pdf->output());
        $lease->update(['moveout_signed_contract_path' => $cachePath]);

        return Storage::disk('public')->download($cachePath, $filename);
    }

    /**
     * Generate (or serve cached) Notice of Termination PDF for the current lease.
     * The notice is a point-in-time document — once a path is stored on the lease
     * we serve the cached file so the tenant and manager always see the same notice.
     */
    public function downloadTerminationNotice()
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::with(['tenant', 'bed.unit.property', 'terminationNoticeViolation'])
            ->find($this->currentLeaseId);

        if (!$lease || !$lease->termination_notice_issued_at) return;

        $tenant = $lease->tenant;
        $unitNumber = $lease->bed->unit->unit_number ?? 'N-A';
        $filename = 'Notice-of-Termination_' . $tenant->first_name . '-' . $tenant->last_name . '_Unit-' . $unitNumber . '.pdf';

        // Serve cached file if we already generated this notice
        if ($lease->termination_notice_path && Storage::disk('public')->exists($lease->termination_notice_path)) {
            return Storage::disk('public')->download($lease->termination_notice_path, $filename);
        }

        $managerId = $this->findManagerIdForLease($lease);
        $manager = $managerId ? User::find($managerId) : null;

        $property = $lease->bed?->unit?->property;
        $noticePeriodDays = (int) ($property?->getContractSetting('termination_notice_period_days', 30) ?? 30);

        // Pull all violations on this lease as the cited grounds (chronological order)
        $groundsViolations = \App\Models\Violation::where('lease_id', $lease->lease_id)
            ->whereNull('deleted_at')
            ->orderBy('violation_date')
            ->get();

        $referenceNumber = 'NOT-' . str_pad((string) $lease->lease_id, 5, '0', STR_PAD_LEFT)
            . '-' . $lease->termination_notice_issued_at->format('Ymd');

        $data = [
            'tenant'             => $this->currentTenant,
            'lease'              => $lease,
            'propertyName'       => $property?->building_name ?? '—',
            'unitNumber'         => $unitNumber,
            'bedNumber'          => $lease->bed?->bed_number,
            'noticePeriodDays'   => $noticePeriodDays,
            'issuedAt'           => $lease->termination_notice_issued_at,
            'referenceNumber'    => $referenceNumber,
            'groundsViolations'  => $groundsViolations,
            'managerName'        => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Property Manager',
        ];

        $pdf = Pdf::loadView('pdf.notice-of-termination', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false);

        $cachePath = 'contracts/notice-of-termination-' . $lease->lease_id . '.pdf';
        Storage::disk('public')->put($cachePath, $pdf->output());
        $lease->update(['termination_notice_path' => $cachePath]);

        return Storage::disk('public')->download($cachePath, $filename);
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
