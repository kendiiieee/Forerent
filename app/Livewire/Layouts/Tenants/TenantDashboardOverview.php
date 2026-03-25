<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class TenantDashboardOverview extends Component
{
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
    public $activePenalties = [];
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

    // Requests & Compliance
    public $openMaintenanceCount = 0;
    public $pendingMaintenanceCount = 0;
    public $ongoingMaintenanceCount = 0;
    public $recentRequests = [];

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

    public function mount()
    {
        $user = Auth::user();
        $this->lease = Lease::with(['bed.unit.property', 'billings.items'])
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if ($this->lease) {
            $this->loadBillingData();
            $this->loadUtilityData();
            $this->loadDepositData();
            $this->loadLeaseData();
            $this->loadMoveData();
            $this->loadMaintenanceData();
            $this->loadContractData();
            $this->loadItemsReceived();
            $this->loadItemsReturned();
        }
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
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
    }

    protected function loadContractData()
    {
        $this->tenantSignature = $this->lease->tenant_signature;
        $this->tenantSignedAt = $this->lease->tenant_signed_at?->format('M d, Y h:i A');
        $this->ownerSignature = $this->lease->owner_signature;
        $this->ownerSignedAt = $this->lease->owner_signed_at?->format('M d, Y h:i A');
        $this->contractAgreed = (bool) $this->lease->contract_agreed;
        $this->signedContractPath = $this->lease->signed_contract_path;

        $user = Auth::user();
        $bed = $this->lease->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;
        $owner = $property?->owner;
        $billing = $this->lease->billings->first();

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
        $this->tenantContractData = [
            'lessor_info' => [
                'business_name'  => $property?->building_name,
                'company_name'   => $owner?->company_school ?? 'CPMS Ventures Corporation',
                'address'        => $property?->address,
                'contact'        => $owner?->contact,
                'email'          => $owner?->email,
                'representative' => $owner ? ($owner->first_name . ' ' . $owner->last_name) : '—',
            ],
            'personal_info' => [
                'first_name'       => $user->first_name,
                'last_name'        => $user->last_name,
                'gender'           => $user->gender,
                'address'          => $property?->address,
                'property'         => $property?->building_name,
                'unit'             => $unit?->unit_number,
                'permanent_address' => $user->permanent_address,
                'government_id_type'   => $user->government_id_type,
                'government_id_number' => $user->government_id_number,
                'government_id_image'  => $user->government_id_image,
                'company_school'       => $user->company_school,
                'position_course'      => $user->position_course,
                'emergency_contact_name'         => $user->emergency_contact_name,
                'emergency_contact_relationship' => $user->emergency_contact_relationship,
                'emergency_contact_number'       => $user->emergency_contact_number,
            ],
            'contact_info' => [
                'contact_number' => $user->contact,
                'email'          => $user->email,
            ],
            'rent_details' => [
                'bed_number'       => $bed?->bed_number,
                'dorm_type'        => $unit?->occupants,
                'floor'            => $unit?->floor_number,
                'room_type'        => $unit?->room_type,
                'lease_start_date' => $this->lease->start_date?->format('Y-m-d'),
                'lease_end_date'   => $this->lease->end_date?->format('Y-m-d'),
                'lease_term'       => $this->lease->term,
                'shift'            => $this->lease->shift,
                'auto_renew'       => $this->lease->auto_renew,
            ],
            'move_in_details' => [
                'move_in_date'          => $this->lease->move_in?->format('Y-m-d'),
                'monthly_rate'          => $this->lease->contract_rate,
                'security_deposit'      => $this->lease->security_deposit,
                'payment_status'        => $billing?->status ?? 'No billing',
                'monthly_due_date'      => $this->lease->monthly_due_date,
                'late_payment_penalty'  => $this->lease->late_payment_penalty,
                'short_term_premium'    => $this->lease->short_term_premium,
                'reservation_fee_paid'  => $this->lease->reservation_fee_paid,
                'early_termination_fee' => $this->lease->early_termination_fee,
            ],
            'move_out_details' => [
                'move_out_date'          => $this->lease->move_out?->format('Y-m-d'),
                'forwarding_address'     => $this->lease->forwarding_address,
                'reason_for_vacating'    => $this->lease->reason_for_vacating,
                'deposit_refund_method'  => $this->lease->deposit_refund_method,
                'deposit_refund_account' => $this->lease->deposit_refund_account,
            ],
        ];
    }

    protected function loadItemsReceived()
    {
        $inspections = MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_received')
            ->get();

        $this->itemsReceived = $inspections->map(fn($i) => [
            'item_name' => $i->item_name,
            'quantity' => $i->quantity,
            'condition' => $i->remarks,
            'tenant_confirmed' => (bool) $i->tenant_confirmed,
        ])->toArray();

        // Check if all items are confirmed by tenant
        $this->itemsConfirmedByTenant = count($this->itemsReceived) > 0
            && collect($this->itemsReceived)->every(fn($item) => $item['tenant_confirmed']);
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

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
        $imageData = base64_decode($imageData);

        $filename = "signatures/{$this->lease->lease_id}_tenant_" . time() . '.png';
        Storage::disk('public')->put($filename, $imageData);

        // Delete old signature file if exists
        if ($this->lease->tenant_signature) {
            Storage::disk('public')->delete($this->lease->tenant_signature);
        }

        $this->lease->update([
            'tenant_signature' => $filename,
            'tenant_signed_at' => now(),
            'tenant_signed_ip' => request()->ip(),
        ]);

        $this->lease->refresh();
        $this->tenantSignature = $filename;
        $this->tenantSignedAt = now()->format('M d, Y h:i A');

        // Check if both signed
        if ($this->lease->tenant_signature && $this->lease->owner_signature) {
            $this->lease->update(['contract_agreed' => true]);
            $this->contractAgreed = true;
        }

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
    }

    public function confirmItemReceived(int $index): void
    {
        if (!isset($this->itemsReceived[$index])) return;

        $this->itemsReceived[$index]['tenant_confirmed'] = true;

        // Update in database
        MoveInInspection::where('lease_id', $this->lease->lease_id)
            ->where('type', 'item_received')
            ->where('item_name', $this->itemsReceived[$index]['item_name'])
            ->update(['tenant_confirmed' => true]);

        // Check if all confirmed
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
    }

    // ===== MOVE-OUT ITEMS RETURNED =====

    protected function loadItemsReturned()
    {
        $moveOutInspections = MoveOutInspection::where('lease_id', $this->lease->lease_id)->get();

        // Items returned
        $returnedItems = $moveOutInspections->where('type', 'item_returned');
        $this->itemsReturned = $returnedItems->map(fn($i) => [
            'item_name' => $i->item_name,
            'quantity' => $i->quantity,
            'condition' => $i->remarks,
            'tenant_confirmed' => (bool) $i->tenant_confirmed,
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
    }

    public function toggleMoveOutContract(): void
    {
        $this->showMoveOutContract = !$this->showMoveOutContract;
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-dashboard-overview');
    }
}
