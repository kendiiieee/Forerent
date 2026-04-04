<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Billing;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentRequests extends Component
{
    use WithPagination;

    public $activeTab = 'Pending';
    public $selectedRequest = null;
    public $showDetailModal = false;
    public $rejectReason = '';
    public $showRejectForm = false;

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
        $this->rejectReason = '';
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRequest = null;
        $this->showRejectForm = false;
        $this->rejectReason = '';
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
            ]);
        });

        $this->closeDetailModal();
    }

    public function toggleRejectForm(): void
    {
        $this->showRejectForm = !$this->showRejectForm;
    }

    public function rejectPayment(): void
    {
        if (!$this->selectedRequest) return;

        $this->validate([
            'rejectReason' => 'required|string|min:5|max:500',
        ], [
            'rejectReason.required' => 'Please provide a reason for rejection.',
            'rejectReason.min' => 'Reason must be at least 5 characters.',
        ]);

        $paymentRequest = PaymentRequest::find($this->selectedRequest['id']);
        if (!$paymentRequest || $paymentRequest->status !== 'Pending') return;

        $paymentRequest->update([
            'status' => 'Rejected',
            'reject_reason' => $this->rejectReason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Notify tenant
        Notification::create([
            'user_id' => $paymentRequest->tenant_id,
            'type' => 'payment_rejected',
            'title' => 'Payment Rejected',
            'message' => 'Your payment of ₱' . number_format($paymentRequest->amount_paid, 2) . ' was rejected. Reason: ' . $this->rejectReason,
        ]);

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

        $query = PaymentRequest::with(['billing', 'tenant', 'lease.bed.unit.property'])
            ->tap($scopeQuery)
            ->where('status', $this->activeTab)
            ->orderBy('created_at', 'desc');

        $requests = $query->paginate(10);

        $counts = [];
        foreach (['Pending', 'Confirmed', 'Rejected'] as $s) {
            $c = PaymentRequest::query()->tap($scopeQuery)->where('status', $s)->count();
            if ($c > 0) $counts[$s] = $c;
        }

        return view('livewire.layouts.financials.payment-requests', [
            'requests' => $requests,
            'counts' => $counts,
        ]);
    }
}
