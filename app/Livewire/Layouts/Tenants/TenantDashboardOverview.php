<?php

namespace App\Livewire\Layouts\Tenants;

use App\Livewire\Concerns\WithContractData;
use App\Livewire\Concerns\WithESignature;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\ContractAuditLog;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TenantDashboardOverview extends Component
{
    use WithESignature, WithContractData, WithFileUploads;
    // Payment & Billing
    public $currentBilling = null;
    public $amountDue = 0;
    public $dueDate = null;
    public $daysUntilDue = 0;
    public $paymentStatus = 'No Billing';
    public $outstandingBalance = 0;
    public $nextPaymentDate = null;
    public $billingItems = [];
    public $billingStartDate = null;
    public $billingProgress = 0;

    // Utility Breakdown
    public $electricityShare = 0;
    public $electricityTotal = 0;
    public $waterShare = 0;
    public $waterTotal = 0;
    public $tenantCount = 0;
    public $billingPeriod = '';

    // Deposit & Fees
    public $securityDeposit = 0;
    public $advanceAmount = 0;
    public $activePenalties;
    public $totalPenalties = 0;

    // Lease & Contract
    public $lease = null;
    public $leaseStatus = 'N/A';
    public $leaseEndDate = null;
    public $daysUntilExpiry = 0;
    public $leaseTerm = 0;
    public $contractRate = 0;
    public $isShortTerm = false;
    public $autoRenew = false;

    // Move-In / Move-Out
    public $moveInDate = null;
    public $moveOutDate = null;
    public $moveOutInitiated = false;

    // Clearance Checklist (dynamic)
    public $billsSettled = false;
    public $inspectionDone = false;

    // Requests & Compliance
    public $openMaintenanceCount = 0;
    public $pendingMaintenanceCount = 0;
    public $ongoingMaintenanceCount = 0;
    public $recentRequests = [];

    // Violations
    public $violations = [];
    public $violationCounts = ['total' => 0, 'issued' => 0, 'acknowledged' => 0, 'resolved' => 0];

    // Contract & E-Signature
    public $showSignatureModal = false;
    public $tenantSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignature = null;
    public $ownerSignedAt = null;
    public $contractAgreed = false;
    public $signedContractPath = null;
    public $showContract = false;
    public $contractData = [];
    public $tenantContractData = []; // full data for the contract modal template

    // Items received confirmation
    public $itemsReceived = [];
    public $itemsConfirmedByTenant = false;

    // Items returned confirmation (move-out)
    public $itemsReturned = [];
    public $itemsReturnedConfirmedByTenant = false;
    public $showMoveOutContract = false;
    public $moveOutChecklist = [];
    public $moveOutInspectionChecklist = []; // move-in checklist for comparison

    // Dashboard tab
    public $dashTab = 'overview';

    // Payment request modal
    public $showPaymentModal = false;
    public $paymentStep = 1; // 1: select billing, 2: select method + instructions, 3: proof form, 4: success
    public $unpaidBillings = [];
    public $selectedBillingId = null;
    public $selectedPaymentMethod = null;
    public $paymentReferenceNumber = '';
    public $paymentAmountPaid = '';
    public $paymentProofImage = null;
    public $paymentOwnerInfo = [];
    public $pendingPaymentRequests = [];
    public $rejectedPaymentRequests = [];
    public $previousProofImagePath = null;
    public $resubmitRejectReason = null;

    // Move-out e-signature (independent from move-in)
    public $showMoveOutSignatureModal = false;
    public $moveOutTenantSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignature = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutContractAgreed = false;

    public function mount()
    {
        $user = Auth::user();

        // Auto-switch tab from query param (e.g. ?tab=inspection)
        $tab = request()->query('tab');
        if ($tab && in_array($tab, ['overview', 'inspection'])) {
            $this->dashTab = $tab;
        }

        // Try active lease first, then fall back to latest expired lease
        $this->lease = Lease::with(['bed.unit.property', 'billings.items'])
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if (!$this->lease) {
            $this->lease = Lease::with(['bed.unit.property', 'billings.items'])
                ->where('tenant_id', $user->user_id)
                ->where('status', 'Expired')
                ->latest()
                ->first();
        }

        if ($this->lease) {
            $this->loadBillingData();
            $this->loadUtilityData();
            $this->loadDepositData();
            $this->loadLeaseData();
            $this->loadMoveData();
            $this->loadMaintenanceData();
            $this->loadViolationData();
            $this->loadContractData();
            $this->loadItemsReceived();
            $this->loadItemsReturned();
            $this->loadClearanceStatus();
            $this->loadPaymentRequests();
        }
    }

    protected function loadClearanceStatus()
    {
        if (!$this->moveOutDate && !$this->moveOutInitiated) return;

        // Bills settled: no unpaid/overdue billings exist for this lease
        $unpaidCount = Billing::where('lease_id', $this->lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->count();
        $this->billsSettled = $unpaidCount === 0;

        // Room inspection done: move-out inspection items exist
        $this->inspectionDone = MoveOutInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_returned')
            ->exists();
    }

    protected function loadBillingData()
    {
        // Current/latest billing
        $this->currentBilling = Billing::with('items')
            ->where('lease_id', $this->lease->lease_id)
            ->orderBy('billing_date', 'desc')
            ->first();

        if ($this->currentBilling) {
            $this->amountDue = $this->currentBilling->to_pay;
            $this->dueDate = $this->currentBilling->due_date;
            $this->billingStartDate = $this->currentBilling->billing_date;
            $this->paymentStatus = $this->currentBilling->status;
            $this->billingItems = $this->currentBilling->items ?? collect();

            if ($this->dueDate) {
                $this->daysUntilDue = Carbon::now()->startOfDay()->diffInDays(
                    Carbon::parse($this->dueDate)->startOfDay(),
                    false
                );
            }

            // Calculate billing period progress for the timeline bar
            if ($this->billingStartDate && $this->currentBilling->next_billing) {
                $start = Carbon::parse($this->billingStartDate)->startOfDay();
                $end = Carbon::parse($this->currentBilling->next_billing)->startOfDay();
                $now = Carbon::now()->startOfDay();
                $totalDays = $start->diffInDays($end);
                $elapsed = $start->diffInDays($now);
                $this->billingProgress = $totalDays > 0 ? min(round(($elapsed / $totalDays) * 100), 100) : 0;
            }
        }

        // Outstanding balance from previous months
        $this->outstandingBalance = Billing::where('lease_id', $this->lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->where('billing_id', '!=', optional($this->currentBilling)->billing_id)
            ->sum('to_pay');

        // Next payment date
        if ($this->currentBilling && $this->currentBilling->next_billing) {
            $this->nextPaymentDate = $this->currentBilling->next_billing;
        }
    }

    protected function loadUtilityData()
    {
        $unit = $this->lease->bed->unit ?? null;
        if (!$unit) return;

        // Current month utilities
        $currentPeriod = Carbon::now()->startOfMonth()->format('Y-m-d');

        $electricity = UtilityBill::where('unit_id', $unit->unit_id)
            ->where('utility_type', 'electricity')
            ->orderBy('billing_period', 'desc')
            ->first();

        $water = UtilityBill::where('unit_id', $unit->unit_id)
            ->where('utility_type', 'water')
            ->orderBy('billing_period', 'desc')
            ->first();

        if ($electricity) {
            $this->electricityShare = $electricity->per_tenant_amount;
            $this->electricityTotal = $electricity->total_amount;
            $this->tenantCount = $electricity->tenant_count;
            $this->billingPeriod = Carbon::parse($electricity->billing_period)->format('M Y');
        }

        if ($water) {
            $this->waterShare = $water->per_tenant_amount;
            $this->waterTotal = $water->total_amount;
            if (!$this->tenantCount && $water->tenant_count) {
                $this->tenantCount = $water->tenant_count;
            }
        }
    }

    protected function loadDepositData()
    {
        $this->securityDeposit = $this->lease->security_deposit ?? 0;
        $this->advanceAmount = $this->lease->advance_amount ?? 0;

        // Get active penalties from billing items
        $this->activePenalties = BillingItem::whereHas('billing', function ($q) {
            $q->where('lease_id', $this->lease->lease_id);
        })
            ->where('charge_category', 'conditional')
            ->whereIn('charge_type', ['late_fee', 'violation_fee', 'short_term_premium'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $this->totalPenalties = $this->activePenalties->sum('amount');
    }

    protected function loadLeaseData()
    {
        $this->leaseStatus = $this->lease->status;
        $this->leaseEndDate = $this->lease->end_date;
        $this->leaseTerm = $this->lease->term;
        $this->contractRate = $this->lease->contract_rate;
        $this->autoRenew = $this->lease->auto_renew;
        $this->isShortTerm = $this->lease->term <= 3;

        if ($this->leaseEndDate) {
            $this->daysUntilExpiry = Carbon::now()->startOfDay()->diffInDays(
                Carbon::parse($this->leaseEndDate)->startOfDay(),
                false
            );
        }
    }

    protected function loadMoveData()
    {
        $this->moveInDate = $this->lease->move_in;
        $this->moveOutDate = $this->lease->move_out;
        $this->moveOutInitiated = (bool) $this->lease->move_out_initiated_at;
    }

    protected function loadMaintenanceData()
    {
        $leaseIds = Auth::user()->leases()->pluck('lease_id');

        $this->openMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->count();

        $this->pendingMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->where('status', 'Pending')
            ->count();

        $this->ongoingMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->where('status', 'Ongoing')
            ->count();

        $this->recentRequests = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
    }

    protected function loadViolationData()
    {
        $leaseIds = Auth::user()->leases()->pluck('lease_id');

        $this->violations = DB::table('violations')
            ->whereIn('lease_id', $leaseIds)
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

    public function acknowledgeViolation(int $violationId): void
    {
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $violation = DB::table('violations')
            ->where('violation_id', $violationId)
            ->whereIn('lease_id', $tenantLeaseIds)
            ->where('status', 'Issued')
            ->whereNull('deleted_at')
            ->first();

        if (!$violation) return;

        DB::table('violations')
            ->where('violation_id', $violationId)
            ->update([
                'status' => 'Acknowledged',
                'tenant_acknowledged_at' => now(),
                'updated_at' => now(),
            ]);

        // Notify manager
        $unit = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $violation->lease_id)
            ->select('units.manager_id')
            ->first();

        if ($unit && $unit->manager_id) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $unit->manager_id,
                'type' => 'violation_acknowledged',
                'title' => 'Violation Acknowledged',
                'message' => "{$user->first_name} {$user->last_name} has acknowledged violation {$violation->violation_number}.",
                'link' => route('manager.tenant'),
            ]);
        }

        $this->loadViolationData();
        $this->dispatch('notify', type: 'success', title: 'Violation Acknowledged', description: 'You have acknowledged this violation notice.');
    }

    public function refreshContractData()
    {
        $this->lease->refresh();
        $this->loadContractData();
    }

    protected function loadContractData()
    {
        $this->loadSignatureState($this->lease);
        $this->signedContractPath = $this->lease->signed_contract_path;

        $user = Auth::user();
        $bed = $this->lease->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;
        $owner = $property?->owner;

        $this->contractData = [
            'lessor' => $owner ? ($owner->first_name . ' ' . $owner->last_name) : '—',
            'company' => $owner?->company_school ?? '—',
            'property' => $property?->building_name ?? '—',
            'unit' => $unit?->unit_number ?? '—',
            'bed' => $bed?->bed_number ?? '—',
            'start_date' => $this->lease->start_date?->format('M d, Y'),
            'end_date' => $this->lease->end_date?->format('M d, Y'),
            'monthly_rate' => $this->lease->contract_rate,
            'security_deposit' => $this->lease->security_deposit,
            'term' => $this->lease->term,
        ];

        // Full data structure matching what the contract modal template expects
        $this->tenantContractData = $this->buildContractDataArray($user, $this->lease);
    }

    protected function loadItemsReceived()
    {
        $inspections = MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_received')
            ->get();

        $this->itemsReceived = $inspections->map(fn($i) => [
            'id' => $i->id,
            'item_name' => $i->item_name,
            'quantity' => $i->quantity,
            'condition' => $i->remarks,
            'tenant_confirmed' => (bool) $i->tenant_confirmed,
            'dispute_status' => $i->dispute_status ?? 'none',
            'resolution_remarks' => $i->resolution_remarks,
        ])->toArray();

        // Check if all items are confirmed by tenant
        $this->itemsConfirmedByTenant = count($this->itemsReceived) > 0
            && collect($this->itemsReceived)->every(fn($item) => $item['tenant_confirmed']);
    }

    public function setDashTab(string $tab): void
    {
        $this->dashTab = $tab;
    }

    protected function loadPaymentRequests(): void
    {
        $this->pendingPaymentRequests = PaymentRequest::where('lease_id', $this->lease->lease_id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'Pending')
            ->with('billing')
            ->latest()
            ->get()
            ->toArray();

        $this->rejectedPaymentRequests = PaymentRequest::where('lease_id', $this->lease->lease_id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'Rejected')
            ->with('billing')
            ->latest()
            ->get()
            ->toArray();
    }

    public function openPaymentModal(): void
    {
        $this->resetPaymentForm();

        // Load unpaid/overdue billings that don't have a pending request
        $pendingBillingIds = PaymentRequest::where('lease_id', $this->lease->lease_id)
            ->where('status', 'Pending')
            ->pluck('billing_id')
            ->toArray();

        $this->unpaidBillings = Billing::where('lease_id', $this->lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->whereNotIn('billing_id', $pendingBillingIds)
            ->orderBy('due_date', 'asc')
            ->get()
            ->toArray();

        // Load owner info for payment instructions
        $property = $this->lease->bed->unit->property ?? null;
        $owner = $property?->owner;
        $this->paymentOwnerInfo = [
            'property_name' => $property?->building_name ?? 'N/A',
            'owner_name' => $owner ? ($owner->first_name . ' ' . $owner->last_name) : 'N/A',
            'contact' => $owner?->contact ?? 'N/A',
        ];

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function selectBilling(int $billingId): void
    {
        $this->selectedBillingId = $billingId;
        $billing = collect($this->unpaidBillings)->firstWhere('billing_id', $billingId);
        if ($billing) {
            $this->paymentAmountPaid = $billing['to_pay'];
        }
        $this->paymentStep = 2;
    }

    public function selectPaymentMethod(string $method): void
    {
        $this->selectedPaymentMethod = $method;
    }

    public function confirmPaymentMethod(): void
    {
        if ($this->selectedPaymentMethod) {
            $this->paymentStep = 3;
        }
    }

    public function goToPaymentStep(int $step): void
    {
        if ($step < $this->paymentStep) {
            $this->paymentStep = $step;
        }
    }

    public function submitPaymentRequest(): void
    {
        $rules = [
            'selectedBillingId' => 'required',
            'selectedPaymentMethod' => 'required|in:GCash,Maya,Bank Transfer',
            'paymentReferenceNumber' => 'required|string|max:100',
            'paymentAmountPaid' => 'required|numeric|min:1',
        ];

        // Only require new proof if no previous proof exists
        if (!$this->previousProofImagePath) {
            $rules['paymentProofImage'] = 'required|image|max:10240';
        } else {
            $rules['paymentProofImage'] = 'nullable|image|max:10240';
        }

        $this->validate($rules, [
            'paymentProofImage.required' => 'Please upload your proof of payment.',
            'paymentReferenceNumber.required' => 'Please enter the reference number from your payment receipt.',
        ]);

        // Use new upload if provided, otherwise keep previous proof
        $proofPath = $this->paymentProofImage
            ? $this->paymentProofImage->store('payment_proofs', 'public')
            : $this->previousProofImagePath;

        PaymentRequest::create([
            'billing_id' => $this->selectedBillingId,
            'lease_id' => $this->lease->lease_id,
            'tenant_id' => Auth::id(),
            'payment_method' => $this->selectedPaymentMethod,
            'reference_number' => $this->paymentReferenceNumber ?: null,
            'amount_paid' => $this->paymentAmountPaid,
            'proof_image' => $proofPath,
            'status' => 'Pending',
        ]);

        // Notify manager
        $this->notifyManagerOfPaymentRequest();

        $this->paymentStep = 4;
        $this->loadPaymentRequests();
    }

    public function resubmitPayment(int $paymentRequestId): void
    {
        $request = PaymentRequest::find($paymentRequestId);
        if (!$request || $request->tenant_id !== Auth::id() || $request->status !== 'Rejected') return;

        // Pre-fill with previous submission data
        $this->selectedBillingId = $request->billing_id;
        $this->selectedPaymentMethod = $request->payment_method;
        $this->paymentReferenceNumber = $request->reference_number ?? '';
        $this->paymentAmountPaid = $request->amount_paid;
        $this->previousProofImagePath = $request->proof_image;
        $this->resubmitRejectReason = $request->reject_reason;
        $this->paymentProofImage = null;

        // Load unpaid billings
        $pendingBillingIds = PaymentRequest::where('lease_id', $this->lease->lease_id)
            ->where('status', 'Pending')
            ->pluck('billing_id')
            ->toArray();

        $this->unpaidBillings = Billing::where('lease_id', $this->lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->whereNotIn('billing_id', $pendingBillingIds)
            ->orWhere('billing_id', $request->billing_id)
            ->orderBy('due_date', 'asc')
            ->get()
            ->toArray();

        $property = $this->lease->bed->unit->property ?? null;
        $owner = $property?->owner;
        $this->paymentOwnerInfo = [
            'property_name' => $property?->building_name ?? 'N/A',
            'owner_name' => $owner ? ($owner->first_name . ' ' . $owner->last_name) : 'N/A',
            'contact' => $owner?->contact ?? 'N/A',
        ];

        // Delete the rejected request so they can resubmit
        $request->delete();
        $this->loadPaymentRequests();

        $this->paymentStep = 3;
        $this->showPaymentModal = true;
    }

    protected function resetPaymentForm(): void
    {
        $this->paymentStep = 1;
        $this->selectedBillingId = null;
        $this->selectedPaymentMethod = null;
        $this->paymentReferenceNumber = '';
        $this->paymentAmountPaid = '';
        $this->paymentProofImage = null;
        $this->previousProofImagePath = null;
        $this->resubmitRejectReason = null;
    }

    protected function notifyManagerOfPaymentRequest(): void
    {
        $user = Auth::user();
        $unit = $this->lease->bed->unit ?? null;
        $billing = Billing::find($this->selectedBillingId);
        $period = $billing?->billing_date ? Carbon::parse($billing->billing_date)->format('M Y') : 'N/A';
        $msg = $user->first_name . ' ' . $user->last_name . ' submitted a payment of ₱' . number_format($this->paymentAmountPaid, 2) . ' for ' . $period . ' billing.';

        $notifyIds = [];

        // Notify the manager if assigned
        if ($unit?->manager_id) {
            $notifyIds[] = $unit->manager_id;
        }

        // Also notify the property owner
        $ownerId = $unit?->property?->owner_id;
        if ($ownerId && !in_array($ownerId, $notifyIds)) {
            $notifyIds[] = $ownerId;
        }

        foreach ($notifyIds as $id) {
            Notification::create([
                'user_id' => $id,
                'type' => 'payment_request',
                'title' => 'Payment Submitted',
                'message' => $msg,
                'link' => '/manager/payment',
            ]);
        }
    }

    public function openSignatureModal(): void
    {
        $this->showSignatureModal = true;
    }

    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
    }

    public function toggleContract(): void
    {
        $this->showContract = !$this->showContract;
    }

    public function saveTenantSignature(string $signatureData): void
    {
        if (!$this->lease) return;

        $result = $this->saveLeaseSignature($this->lease, $signatureData, 'tenant', 'movein');
        $this->tenantSignature = $result['signature'];
        $this->tenantSignedAt = $result['signedAt'];
        $this->contractAgreed = $result['agreed'];

        // Notify the manager that the tenant signed the contract
        $this->notifyManagerOfSign($this->lease, 'move-in');

        // If contract is now fully executed (tenant signed last), notify manager
        if ($result['agreed']) {
            $this->lease->refresh();
            $this->signedContractPath = $this->lease->signed_contract_path;

            $managerId = $this->findManagerIdForLease($this->lease);
            if ($managerId) {
                $user = Auth::user();
                Notification::create([
                    'user_id' => $managerId,
                    'type' => 'contract_executed',
                    'title' => 'Contract Fully Executed',
                    'message' => $user->first_name . ' ' . $user->last_name . '\'s move-in contract is now fully signed by both parties.',
                    'link' => '/manager/tenant',
                ]);
            }
        }

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
    }

    public function confirmItemReceived(int $index): void
    {
        if (!isset($this->itemsReceived[$index])) return;

        $this->itemsReceived[$index]['tenant_confirmed'] = true;

        MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_received')
            ->where('item_name', $this->itemsReceived[$index]['item_name'])
            ->update(['tenant_confirmed' => true]);

        ContractAuditLog::log($this->lease->lease_id, 'item_confirmed', [
            'field_changed' => $this->itemsReceived[$index]['item_name'],
            'metadata' => ['type' => 'move_in_item'],
        ]);

        $this->itemsConfirmedByTenant = collect($this->itemsReceived)
            ->every(fn($item) => $item['tenant_confirmed']);
    }

    public function confirmAllItems(): void
    {
        MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_received')
            ->update(['tenant_confirmed' => true]);

        foreach ($this->itemsReceived as &$item) {
            $item['tenant_confirmed'] = true;
        }
        $this->itemsConfirmedByTenant = true;

        ContractAuditLog::log($this->lease->lease_id, 'all_items_confirmed', [
            'metadata' => ['type' => 'move_in_items', 'count' => count($this->itemsReceived)],
        ]);

        // Notify manager
        $managerId = $this->findManagerIdForLease($this->lease);
        if ($managerId) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $managerId,
                'type' => 'items_confirmed',
                'title' => 'Items Received Confirmed',
                'message' => $user->first_name . ' ' . $user->last_name . ' has confirmed all move-in items received.',
                'link' => '/manager/tenant',
            ]);
        }
    }

    // ===== MOVE-OUT ITEMS RETURNED =====

    public function refreshMoveOutData()
    {
        $this->lease->refresh();
        $this->loadItemsReturned();
    }

    protected function loadItemsReturned()
    {
        $moveOutInspections = MoveOutInspection::where('lease_id', $this->lease->lease_id)->get();

        // Items returned
        $returnedItems = $moveOutInspections->where('type', 'item_returned');
        $this->itemsReturned = $returnedItems->map(fn($i) => [
            'id' => $i->id,
            'item_name' => $i->item_name,
            'quantity' => $i->quantity,
            'condition' => $i->remarks,
            'tenant_confirmed' => (bool) $i->tenant_confirmed,
            'dispute_status' => $i->dispute_status ?? 'none',
            'resolution_remarks' => $i->resolution_remarks,
        ])->toArray();

        $this->itemsReturnedConfirmedByTenant = count($this->itemsReturned) > 0
            && collect($this->itemsReturned)->every(fn($item) => $item['tenant_confirmed']);

        // Move-out checklist for contract display
        $checklistItems = $moveOutInspections->where('type', 'checklist');
        $this->moveOutChecklist = $checklistItems->map(fn($i) => [
            'item_name' => $i->item_name,
            'condition' => $i->condition,
            'remarks' => $i->remarks,
        ])->toArray();

        // Move-in checklist for comparison
        $moveInChecklist = MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'checklist')
            ->get();
        $this->moveOutInspectionChecklist = $moveInChecklist->map(fn($i) => [
            'item_name' => $i->item_name,
            'condition' => $i->condition,
            'remarks' => $i->remarks,
        ])->toArray();
    }

    public function confirmItemReturned(int $index): void
    {
        if (!isset($this->itemsReturned[$index])) return;

        $this->itemsReturned[$index]['tenant_confirmed'] = true;

        MoveOutInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_returned')
            ->where('item_name', $this->itemsReturned[$index]['item_name'])
            ->update(['tenant_confirmed' => true]);

        ContractAuditLog::log($this->lease->lease_id, 'item_confirmed', [
            'field_changed' => $this->itemsReturned[$index]['item_name'],
            'metadata' => ['type' => 'move_out_item'],
        ]);

        $this->itemsReturnedConfirmedByTenant = collect($this->itemsReturned)
            ->every(fn($item) => $item['tenant_confirmed']);
    }

    public function confirmAllReturned(): void
    {
        MoveOutInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_returned')
            ->update(['tenant_confirmed' => true]);

        foreach ($this->itemsReturned as &$item) {
            $item['tenant_confirmed'] = true;
        }
        $this->itemsReturnedConfirmedByTenant = true;

        ContractAuditLog::log($this->lease->lease_id, 'all_items_confirmed', [
            'metadata' => ['type' => 'move_out_items', 'count' => count($this->itemsReturned)],
        ]);

        // Notify manager that tenant confirmed all returned items
        $managerId = $this->findManagerIdForLease($this->lease);
        if ($managerId) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $managerId,
                'type' => 'items_confirmed',
                'title' => 'Returned Items Confirmed',
                'message' => $user->first_name . ' ' . $user->last_name . ' has confirmed all move-out items returned.',
                'link' => '/manager/tenant',
            ]);
        }
    }

    public function toggleMoveOutContract(): void
    {
        $this->showMoveOutContract = !$this->showMoveOutContract;
    }

    public function openMoveOutSignatureModal(): void
    {
        $this->showMoveOutSignatureModal = true;
    }

    public function closeMoveOutSignatureModal(): void
    {
        $this->showMoveOutSignatureModal = false;
    }

    public function saveMoveOutTenantSignature(string $signatureData): void
    {
        if (!$this->lease) return;

        $result = $this->saveLeaseSignature($this->lease, $signatureData, 'tenant', 'moveout');
        $this->moveOutTenantSignature = $result['signature'];
        $this->moveOutTenantSignedAt = $result['signedAt'];
        $this->moveOutContractAgreed = $result['agreed'];

        // Notify the manager that the tenant signed the move-out contract
        $this->notifyManagerOfSign($this->lease, 'move-out');

        // Reload contract data so outstanding balances / deposit refund are fresh
        $this->lease->refresh();
        $this->loadContractData();

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');
    }

    // ===== TENANT DISPUTE WORKFLOW =====

    public function disputeInspectionItem(int $inspectionId, string $remarks): void
    {
        $item = MoveInInspection::where('id', $inspectionId)
            ->where('lease_id', $this->lease->lease_id)
            ->first();

        if (!$item || $item->dispute_status === 'disputed') return;

        $item->update([
            'dispute_status' => 'disputed',
            'dispute_remarks' => $remarks,
            'disputed_at' => now(),
        ]);

        // Audit log
        ContractAuditLog::log($this->lease->lease_id, 'item_disputed', [
            'field_changed' => $item->item_name,
            'new_value' => $remarks,
            'metadata' => [
                'inspection_type' => 'move_in',
                'item_type' => $item->type,
            ],
        ]);

        // Notify manager
        $managerId = $this->findManagerIdForLease($this->lease);

        if ($managerId) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $managerId,
                'type' => 'inspection_disputed',
                'title' => 'Inspection Item Disputed',
                'message' => $user->first_name . ' ' . $user->last_name . ' has disputed "' . $item->item_name . '": ' . $remarks,
                'link' => '/manager/tenant',
            ]);
        }

        $this->loadItemsReceived();
        $this->dispatch('notify', type: 'info', title: 'Dispute Submitted', description: 'Your dispute has been submitted. The manager will review it.');
    }

    public function disputeMoveOutItem(int $inspectionId, string $remarks): void
    {
        $item = MoveOutInspection::where('id', $inspectionId)
            ->where('lease_id', $this->lease->lease_id)
            ->first();

        if (!$item || $item->dispute_status === 'disputed') return;

        $item->update([
            'dispute_status' => 'disputed',
            'dispute_remarks' => $remarks,
            'disputed_at' => now(),
        ]);

        ContractAuditLog::log($this->lease->lease_id, 'item_disputed', [
            'field_changed' => $item->item_name,
            'new_value' => $remarks,
            'metadata' => [
                'inspection_type' => 'move_out',
                'item_type' => $item->type,
            ],
        ]);

        $managerId = $this->findManagerIdForLease($this->lease);

        if ($managerId) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $managerId,
                'type' => 'inspection_disputed',
                'title' => 'Move-Out Item Disputed',
                'message' => $user->first_name . ' ' . $user->last_name . ' has disputed "' . $item->item_name . '": ' . $remarks,
                'link' => '/manager/tenant',
            ]);
        }

        $this->loadItemsReturned();
        $this->dispatch('notify', type: 'info', title: 'Dispute Submitted', description: 'Your dispute has been submitted. The manager will review it.');
    }

    // ===== CONTRACT DOWNLOAD FOR TENANT =====

    public function downloadSignedContract()
    {
        if (!$this->lease?->signed_contract_path) return;

        return Storage::disk('public')->download(
            $this->lease->signed_contract_path,
            'Move-In-Contract_' . Auth::user()->first_name . '-' . Auth::user()->last_name . '_Unit-' . ($this->lease->bed->unit->unit_number ?? 'N-A') . '.pdf'
        );
    }

    public function downloadMoveOutSignedContract()
    {
        if (!$this->lease?->moveout_signed_contract_path) return;

        return Storage::disk('public')->download(
            $this->lease->moveout_signed_contract_path,
            'Move-Out-Contract_' . Auth::user()->first_name . '-' . Auth::user()->last_name . '_Unit-' . ($this->lease->bed->unit->unit_number ?? 'N-A') . '.pdf'
        );
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-dashboard-overview');
    }
}
