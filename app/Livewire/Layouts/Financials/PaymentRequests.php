<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Billing;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\Property;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithNotifications;

class PaymentRequests extends Component
{
    use WithPagination, WithNotifications;

    public $activeTab = 'All';
    public $selectedRequest = null;
    public $showDetailModal = false;
    public $rejectReasons = [];
    public $rejectOtherReason = '';
    public $showRejectForm = false;

    // Filters
    public $search = '';
    public $selectedMonth = '';
    public $selectedBuilding = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBuilding(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function viewRequest(int $id): void
    {
        $request = PaymentRequest::with(['billing', 'tenant', 'lease.bed.unit.property', 'reviewer'])->find($id);
        if (!$request) return;

        $this->selectedRequest = $request->toArray();
        $this->selectedRequest['tenant_name'] = $request->tenant ? ($request->tenant->first_name . ' ' . $request->tenant->last_name) : 'N/A';
        $this->selectedRequest['reviewer_name'] = $request->reviewer ? ($request->reviewer->first_name . ' ' . $request->reviewer->last_name) : null;

        $bed = $request->lease?->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;
        $this->selectedRequest['unit_number'] = $unit?->unit_number ?? 'N/A';
        $this->selectedRequest['bed_number'] = $bed?->bed_number ?? 'N/A';
        $this->selectedRequest['property_name'] = $property?->building_name ?? 'N/A';
        $this->selectedRequest['billing_period'] = $request->billing?->billing_date ? Carbon::parse($request->billing->billing_date)->format('F Y') : 'N/A';
        $this->selectedRequest['billing_amount'] = $request->billing?->to_pay ?? 0;
        $this->selectedRequest['billing_due'] = $request->billing?->due_date ? Carbon::parse($request->billing->due_date)->format('M d, Y') : 'N/A';

        $this->showRejectForm = false;
        $this->rejectReasons = [];
        $this->rejectOtherReason = '';
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRequest = null;
        $this->showRejectForm = false;
        $this->rejectReasons = [];
        $this->rejectOtherReason = '';
    }

    public function confirmPayment(): void
    {
        if (!$this->selectedRequest) return;

        $paymentRequest = PaymentRequest::find($this->selectedRequest['id']);
        if (!$paymentRequest || $paymentRequest->status !== 'Pending') return;

        DB::transaction(function () use ($paymentRequest) {
            // Update payment request
            $paymentRequest->update([
                'status' => 'Confirmed',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Update billing status to Paid
            $billing = Billing::lockForUpdate()->find($paymentRequest->billing_id);
            if ($billing) {
                $billing->update(['status' => 'Paid']);
            }

            // Create transaction record
            $category = match ($billing?->billing_type) {
                'move_in' => 'Advance',
                'move_out' => 'Deposit',
                default => 'Rent Payment',
            };

            $prefix = match ($category) {
                'Advance' => 'ADV',
                'Deposit' => 'DEP',
                default => 'RENT',
            };

            Transaction::createWithSequenceRetry([
                'billing_id' => $paymentRequest->billing_id,
                'name' => 'Payment #' . $paymentRequest->id . ' - ' . $paymentRequest->payment_method,
                'reference_number' => $paymentRequest->reference_number ?: sprintf('%s%s-%06d', $prefix, now()->format('YmdHis'), $paymentRequest->id),
                'transaction_type' => 'Debit',
                'category' => $category,
                'payment_method' => $paymentRequest->payment_method,
                'transaction_date' => now()->toDateString(),
                'amount' => $paymentRequest->amount_paid,
                'is_recurring' => false,
            ]);

            // Notify tenant
            Notification::create([
                'user_id' => $paymentRequest->tenant_id,
                'type' => 'payment_confirmed',
                'title' => 'Payment Confirmed',
                'message' => 'Your payment of ₱' . number_format($paymentRequest->amount_paid, 2) . ' has been verified and confirmed.',
                'link' => '/tenant/payment',
            ]);
        });

        $this->notifySuccess('Payment Confirmed', 'The payment has been verified and confirmed.');
        $this->closeDetailModal();
    }

    public function toggleRejectForm(): void
    {
        $this->showRejectForm = !$this->showRejectForm;

        if (!$this->showRejectForm) {
            $this->rejectReasons = [];
            $this->rejectOtherReason = '';
            $this->resetValidation();
        }
    }

    public function rejectPayment(): void
    {
        if (!$this->selectedRequest) return;

        $rules = [
            'rejectReasons' => 'required|array|min:1',
        ];
        $messages = [
            'rejectReasons.required' => 'Please select at least one reason for rejection.',
            'rejectReasons.min' => 'Please select at least one reason for rejection.',
        ];

        if (in_array('Other', $this->rejectReasons)) {
            $rules['rejectOtherReason'] = 'required|string|min:5|max:500';
            $messages['rejectOtherReason.required'] = 'Please specify the other reason.';
            $messages['rejectOtherReason.min'] = 'Other reason must be at least 5 characters.';
        }

        $this->validate($rules, $messages);

        // Build the rejection reason string
        $reasons = collect($this->rejectReasons)
            ->map(fn($r) => $r === 'Other' ? 'Other: ' . $this->rejectOtherReason : $r)
            ->implode('; ');

        $paymentRequest = PaymentRequest::find($this->selectedRequest['id']);
        if (!$paymentRequest || $paymentRequest->status !== 'Pending') return;

        $paymentRequest->update([
            'status' => 'Rejected',
            'reject_reason' => $reasons,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Notify tenant
        Notification::create([
            'user_id' => $paymentRequest->tenant_id,
            'type' => 'payment_rejected',
            'title' => 'Payment Rejected',
            'message' => 'Your payment of ₱' . number_format($paymentRequest->amount_paid, 2) . ' was rejected. Reason: ' . $reasons,
            'link' => '/tenant/payment',
        ]);

        $this->notifySuccess('Payment Rejected', 'The payment request has been rejected and the tenant has been notified.');
        $this->closeDetailModal();
    }

    public function render()
    {
        $user = Auth::user();

        // Scope payment requests based on user role
        $scopeQuery = function ($query) use ($user) {
            if ($user->role === 'manager') {
                $query->whereHas('lease.bed.unit', fn($q) => $q->where('manager_id', $user->user_id));
            } elseif ($user->role === 'landlord') {
                $query->whereHas('lease.bed.unit.property', fn($q) => $q->where('owner_id', $user->user_id));
            }
        };

        // Build base query with filters
        $applyFilters = function ($query) {
            // Search filter
            if (!empty($this->search)) {
                $search = '%' . $this->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereHas('tenant', fn($t) => $t->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$search]))
                      ->orWhere('reference_number', 'like', $search)
                      ->orWhere('payment_method', 'like', $search);
                });
            }

            // Month filter
            if (!empty($this->selectedMonth)) {
                $query->whereHas('billing', fn($b) => $b->whereRaw("TO_CHAR(billing_date, 'YYYY-MM') = ?", [$this->selectedMonth]));
            }

            // Building filter
            if (!empty($this->selectedBuilding)) {
                $query->whereHas('lease.bed.unit.property', fn($p) => $p->where('building_name', $this->selectedBuilding));
            }
        };

        $query = PaymentRequest::with(['billing', 'tenant', 'lease.bed.unit.property'])
            ->tap($scopeQuery)
            ->tap($applyFilters)
            ->when($this->activeTab !== 'All', fn($q) => $q->where('status', $this->activeTab))
            ->orderBy('created_at', 'desc');

        $requests = $query->paginate(10);

        $counts = ['All' => PaymentRequest::query()->tap($scopeQuery)->tap($applyFilters)->count()];
        foreach (['Pending', 'Confirmed', 'Rejected'] as $s) {
            $counts[$s] = PaymentRequest::query()->tap($scopeQuery)->tap($applyFilters)->where('status', $s)->count();
        }

        // Building options for dropdown
        $buildingOptions = Property::distinct()->pluck('building_name', 'building_name')->toArray();

        // Month options from existing payment requests
        $monthOptions = PaymentRequest::query()
            ->tap($scopeQuery)
            ->join('billings', 'payment_requests.billing_id', '=', 'billings.billing_id')
            ->selectRaw("DISTINCT TO_CHAR(billings.billing_date, 'YYYY-MM') as month_value, TO_CHAR(billings.billing_date, 'FMMonth YYYY') as month_label")
            ->orderByDesc('month_value')
            ->pluck('month_label', 'month_value')
            ->toArray();

        return view('livewire.layouts.financials.payment-requests', [
            'requests' => $requests,
            'counts' => $counts,
            'buildingOptions' => $buildingOptions,
            'monthOptions' => $monthOptions,
        ]);
    }
}
