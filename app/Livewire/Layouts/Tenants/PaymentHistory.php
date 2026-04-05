<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Notification;
use App\Models\PaymentRequest;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Livewire\Concerns\WithNotifications;

class PaymentHistory extends Component
{
    use WithPagination, WithFileUploads, WithNotifications;

    public $activeTab = 'all';
    public $search = '';
    public $sortOrder = 'newest';

    // Payment banner
    public $amountDue = 0;
    public $dueDate = null;
    public $daysUntilDue = 0;
    public $paymentStatus = 'No Billing';
    public $pendingPaymentRequests = [];
    public $rejectedPaymentRequests = [];

    // Payment modal
    public $showPaymentModal = false;
    public $paymentStep = 1;
    public $unpaidBillings = [];
    public $selectedBillingId = null;
    public $selectedPaymentMethod = null;
    public $paymentReferenceNumber = '';
    public $paymentAmountPaid = '';
    public $paymentProofImage = null;
    public $paymentOwnerInfo = [];
    public $previousProofImagePath = null;
    public $resubmitRejectReason = null;

    protected function getLease()
    {
        return Lease::with(['bed.unit.property.owner'])
            ->where('tenant_id', Auth::user()->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();
    }

    public function setTab($tab) { $this->activeTab = $tab; $this->resetPage(); }
    public function updatedActiveTab() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function mount()
    {
        $this->loadBannerData();
    }

    protected function loadBannerData(): void
    {
        $lease = $this->getLease();
        if (!$lease) return;

        $billing = Billing::where('lease_id', $lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        if ($billing) {
            $this->amountDue = $billing->to_pay;
            $this->dueDate = $billing->due_date;
            $this->daysUntilDue = $billing->due_date ? Carbon::parse($billing->due_date)->diffInDays(now(), false) * -1 : 0;
            $this->paymentStatus = $billing->status;
        } else {
            $paidBilling = Billing::where('lease_id', $lease->lease_id)
                ->where('status', 'Paid')
                ->latest('billing_date')
                ->first();
            if ($paidBilling) {
                $this->amountDue = $paidBilling->to_pay;
                $this->paymentStatus = 'Paid';
            }
        }

        $this->loadPaymentRequests();
    }

    protected function loadPaymentRequests(): void
    {
        $lease = $this->getLease();
        if (!$lease) return;

        $this->pendingPaymentRequests = PaymentRequest::where('lease_id', $lease->lease_id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'Pending')
            ->with('billing')
            ->latest()
            ->get()
            ->toArray();

        $this->rejectedPaymentRequests = PaymentRequest::where('lease_id', $lease->lease_id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'Rejected')
            ->with('billing')
            ->latest()
            ->get()
            ->toArray();
    }

    public function openPaymentModal(): void
    {
        $lease = $this->getLease();
        if (!$lease) return;

        $this->resetPaymentForm();

        $pendingBillingIds = PaymentRequest::where('lease_id', $lease->lease_id)
            ->where('status', 'Pending')
            ->pluck('billing_id')
            ->toArray();

        $this->unpaidBillings = Billing::where('lease_id', $lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->whereNotIn('billing_id', $pendingBillingIds)
            ->orderBy('due_date', 'asc')
            ->get()
            ->toArray();

        $property = $lease->bed->unit->property ?? null;
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

        if (!$this->previousProofImagePath) {
            $rules['paymentProofImage'] = 'required|image|max:10240';
        } else {
            $rules['paymentProofImage'] = 'nullable|image|max:10240';
        }

        $this->validate($rules, [
            'paymentProofImage.required' => 'Please upload your proof of payment.',
            'paymentReferenceNumber.required' => 'Please enter the reference number from your payment receipt.',
        ]);

        $proofPath = $this->paymentProofImage
            ? $this->paymentProofImage->store('payment_proofs', 'public')
            : $this->previousProofImagePath;

        $lease = $this->getLease();

        PaymentRequest::create([
            'billing_id' => $this->selectedBillingId,
            'lease_id' => $lease->lease_id,
            'tenant_id' => Auth::id(),
            'payment_method' => $this->selectedPaymentMethod,
            'reference_number' => $this->paymentReferenceNumber ?: null,
            'amount_paid' => $this->paymentAmountPaid,
            'proof_image' => $proofPath,
            'status' => 'Pending',
        ]);

        $this->notifyManagerOfPaymentRequest();

        $this->notifySuccess('Payment Submitted', 'Your payment request has been submitted and is awaiting confirmation.');
        $this->paymentStep = 4;
        $this->loadPaymentRequests();
        $this->loadBannerData();
    }

    public function resubmitPayment(int $paymentRequestId): void
    {
        $lease = $this->getLease();
        if (!$lease) return;

        $request = PaymentRequest::find($paymentRequestId);
        if (!$request || $request->tenant_id !== Auth::id() || $request->status !== 'Rejected') return;

        $this->selectedBillingId = $request->billing_id;
        $this->selectedPaymentMethod = $request->payment_method;
        $this->paymentReferenceNumber = $request->reference_number ?? '';
        $this->paymentAmountPaid = $request->amount_paid;
        $this->previousProofImagePath = $request->proof_image;
        $this->resubmitRejectReason = $request->reject_reason;
        $this->paymentProofImage = null;

        $pendingBillingIds = PaymentRequest::where('lease_id', $lease->lease_id)
            ->where('status', 'Pending')
            ->pluck('billing_id')
            ->toArray();

        $this->unpaidBillings = Billing::where('lease_id', $lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->whereNotIn('billing_id', $pendingBillingIds)
            ->orWhere('billing_id', $request->billing_id)
            ->orderBy('due_date', 'asc')
            ->get()
            ->toArray();

        $property = $lease->bed->unit->property ?? null;
        $owner = $property?->owner;
        $this->paymentOwnerInfo = [
            'property_name' => $property?->building_name ?? 'N/A',
            'owner_name' => $owner ? ($owner->first_name . ' ' . $owner->last_name) : 'N/A',
            'contact' => $owner?->contact ?? 'N/A',
        ];

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
        $lease = $this->getLease();
        $unit = $lease?->bed->unit ?? null;
        $billing = Billing::find($this->selectedBillingId);
        $period = $billing?->billing_date ? Carbon::parse($billing->billing_date)->format('M Y') : 'N/A';
        $msg = $user->first_name . ' ' . $user->last_name . ' submitted a payment of ₱' . number_format($this->paymentAmountPaid, 2) . ' for ' . $period . ' billing.';

        $notifyIds = [];

        if ($unit?->manager_id) {
            $notifyIds[] = $unit->manager_id;
        }

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

    public function viewReceipt($billingId)
    {
        $record = DB::table('billings')
            ->join('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users as tenant', 'leases.tenant_id', '=', 'tenant.user_id')
            ->join('users as manager', 'units.manager_id', '=', 'manager.user_id')
            ->leftJoin('transactions', function ($join) {
                $join->on('transactions.billing_id', '=', 'billings.billing_id')
                    ->whereIn('transactions.category', ['Rent Payment', 'Advance', 'Deposit']);
            })
            ->where('billings.billing_id', $billingId)
            ->where('leases.tenant_id', Auth::user()->user_id)
            ->select(
                'billings.billing_id',
                'billings.billing_date',
                'billings.billing_type',
                'billings.due_date',
                'billings.to_pay',
                'billings.previous_balance',
                'billings.status',
                'units.unit_number',
                'units.room_cap',
                'units.occupants',
                'units.bed_type',
                'beds.bed_number',
                'properties.building_name',
                'properties.address',
                'leases.start_date',
                'leases.end_date',
                'leases.term',
                'tenant.first_name as tenant_first_name',
                'tenant.last_name as tenant_last_name',
                'tenant.contact as tenant_contact',
                'manager.first_name as manager_first_name',
                'manager.last_name as manager_last_name',
                'manager.contact as manager_contact',
                'transactions.transaction_date as txn_date',
                'transactions.reference_number as txn_reference',
                'transactions.payment_method as txn_payment_method',
                'transactions.or_number as txn_or_number',
            )
            ->first();

        if (!$record) return;

        $billingDate = Carbon::parse($record->billing_date);
        $dueDate = $record->due_date
            ? Carbon::parse($record->due_date)->format('F d, Y')
            : $billingDate->copy()->addDays(20)->format('F d, Y');

        // Fetch billing items
        $billingItems = BillingItem::where('billing_id', $billingId)
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($item) => [
                'description' => $item->description,
                'amount'      => $item->amount,
                'category'    => $item->charge_category,
                'type'        => $item->charge_type,
            ])
            ->toArray();

        // Fallback if no billing items exist (legacy data)
        if (empty($billingItems)) {
            $billingItems = [[
                'description' => 'Unit ' . $record->unit_number . ' - Monthly Rent',
                'amount'      => $record->to_pay,
                'category'    => 'recurring',
                'type'        => 'rent',
            ]];
        }

        $data = [
            'invoice_no'    => '20250825-' . str_pad($record->billing_id, 3, '0', STR_PAD_LEFT),
            'issued_date'   => $billingDate->format('F d, Y'),
            'due_date'      => $dueDate,
            'status'        => $record->status,
            'billing_type'  => $record->billing_type ?? 'monthly',
            'previous_balance' => $record->previous_balance ?? 0,
            'tenant' => [
                'name'         => $record->tenant_first_name . ' ' . $record->tenant_last_name,
                'unit_bed'     => 'Unit ' . $record->unit_number . ' — ' . $record->bed_number,
                'room_type'    => $record->room_cap . '-in-a-Room Bedspace (' . $record->occupants . ')',
                'building'     => $record->building_name,
                'location'     => $record->address,
                'lease_period' => Carbon::parse($record->start_date)->format('M d') . ' — ' . Carbon::parse($record->end_date)->format('M d, Y'),
                'lease_type'   => $record->term . '-Month Contract',
            ],
            'payment' => [
                'date_paid'       => $record->txn_date ? Carbon::parse($record->txn_date)->format('F d, Y') : 'Pending',
                'payment_method'  => $record->txn_payment_method ?? 'Pending',
                'txn_id'          => $record->txn_payment_method
                    ? ['GCash' => 'GC', 'Maya' => 'MY', 'Bank Transfer' => 'BT', 'Cash' => 'CS'][$record->txn_payment_method] . '-' . mt_rand(1000000000, 9999999999)
                    : 'Pending',
                'reference_no'    => $record->txn_reference ?? 'Pending',
                'or_number'       => $record->txn_or_number ?? 'Pending',
                'period'          => $billingDate->format('F Y'),
            ],
            'recipient' => [
                'name'     => $record->manager_first_name . ' ' . $record->manager_last_name,
                'position' => 'Property Manager',
                'contact'  => $record->manager_contact ?? 'N/A',
            ],
            'items' => $billingItems,
            'total' => $record->to_pay,
            'financials' => [
                'description' => 'Unit ' . $record->unit_number . ' - Monthly Rent',
                'amount'      => $record->to_pay,
            ],
        ];

        $this->dispatch('open-payment-receipt', data: $data);
    }

    private function baseQuery()
    {
        return DB::table('billings')
            ->join('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->leftJoin('transactions', function ($join) {
                $join->on('transactions.billing_id', '=', 'billings.billing_id')
                    ->whereNull('transactions.deleted_at');
            })
            ->where('leases.tenant_id', Auth::user()->user_id)
            ->whereNull('billings.deleted_at')
            ->whereNull('leases.deleted_at')
            ->select(
                'billings.billing_id',
                'billings.billing_date',
                'billings.billing_type',
                'billings.to_pay',
                'billings.status',
                'properties.building_name',
                'transactions.reference_number',
                'transactions.category',
                'transactions.transaction_date',
                'transactions.amount as transaction_amount'
            );
    }

    public function render()
    {
        $baseQuery = $this->baseQuery();

        // Apply search filter
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($search) {
                $q->where('transactions.reference_number', 'like', $search)
                  ->orWhere('transactions.category', 'like', $search)
                  ->orWhere('billings.status', 'like', $search)
                  ->orWhere('properties.building_name', 'like', $search);
            });
        }

        $counts = [
            'all'      => (clone $baseQuery)->count(),
            'upcoming' => (clone $baseQuery)->where('billings.status', 'Unpaid')->count(),
            'paid'     => (clone $baseQuery)->where('billings.status', 'Paid')->count(),
            'unpaid'   => (clone $baseQuery)->where('billings.status', 'Overdue')->count(),
        ];

        $query = clone $baseQuery;

        match ($this->activeTab) {
            'upcoming' => $query->where('billings.status', 'Unpaid'),
            'paid'     => $query->where('billings.status', 'Paid'),
            'unpaid'   => $query->where('billings.status', 'Overdue'),
            default    => null,
        };

        $direction = $this->sortOrder === 'oldest' ? 'asc' : 'desc';
        $payments = $query->orderBy('billings.billing_date', $direction)->paginate(10);

        // Build suggestions from unfiltered data
        $allRecords = $this->baseQuery()->get();
        $suggestions = collect()
            ->merge($allRecords->pluck('reference_number')->filter())
            ->merge($allRecords->pluck('category')->filter())
            ->merge($allRecords->pluck('building_name')->filter())
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.tenants.payment-history', [
            'payments'    => $payments,
            'counts'      => $counts,
            'suggestions' => $suggestions,
        ]);
    }
}
