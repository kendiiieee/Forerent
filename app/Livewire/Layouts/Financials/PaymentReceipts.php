<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentReceipts extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $filterPeriod = '';
    public $filterBuilding = '';
    public $billingIdToMarkPaid = null;

    public function updatedActiveTab()   { $this->resetPage(); }
    public function updatedFilterPeriod() { $this->resetPage(); }
    public function updatedFilterBuilding() { $this->resetPage(); }

    public function confirmPayment($id)
    {
        $this->billingIdToMarkPaid = $id;
        $this->dispatch('open-modal', 'mark-as-paid-confirmation');
    }

    public function viewReceipt($billingId)
    {
        logger("Click registered for Billing ID: " . $billingId);
        $this->dispatch('open-payment-receipt', billingId: $billingId);
    }

    public function markAsPaid()
    {
        if ($this->billingIdToMarkPaid) {
            DB::table('billings')
                ->where('billing_id', $this->billingIdToMarkPaid)
                ->update([
                    'status'     => 'Paid',
                    'amount'     => DB::raw('to_pay'),
                    'updated_at' => now(),
                ]);

            // Fetch the updated billing record
            $billing = DB::table('billings')->where('billing_id', $this->billingIdToMarkPaid)->first();

            $transaction = Transaction::create([
                'billing_id'       => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Rent Payment',
                'transaction_date' => today(),
                'amount'           => $billing->amount ?? 0,
            ]);

            $transaction->update([
                'reference_number' => 'RENT' . now()->format('Ymd') . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            $this->dispatch('show-toast', ['message' => 'Payment marked as Paid!']);
            $this->dispatch('close-modal', 'mark-as-paid-confirmation');
            $this->billingIdToMarkPaid = null;
        }
    }

    // ─── helpers ────────────────────────────────────────────────────────────

    private function isManager(): bool
    {
        return Auth::user()?->role === 'manager'; // adjust to your role field/check
    }

    private function isTenant(): bool
    {
        return Auth::user()?->role === 'tenant'; // adjust to your role field/check
    }

    private function baseQuery()
    {
        $query = DB::table('billings')
            ->join('leases',  'billings.lease_id',  '=', 'leases.lease_id')
            ->join('beds',    'leases.bed_id',       '=', 'beds.bed_id')
            ->join('units',   'beds.unit_id',        '=', 'units.unit_id')
            ->join('users',   'leases.tenant_id',    '=', 'users.user_id')
            ->select('billings.*', 'users.first_name', 'users.last_name');

        if ($this->isManager()) {
            $query->where('units.manager_id', Auth::id());
        }

        if ($this->isTenant()) {
            $query->where('leases.tenant_id', Auth::id());
        }

        return $query;
    }

    // ─── render ─────────────────────────────────────────────────────────────

    public function render()
    {
        $baseQuery = $this->baseQuery();

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

        if ($this->filterPeriod) {
            $query->whereMonth('billings.billing_date', $this->filterPeriod);
        }

        $payments = $query->orderBy('billings.billing_date', 'desc')->paginate(10);

        return view('livewire.layouts.financials.payment-receipts', [
            'payments' => $payments,
            'counts' => $counts,
        ]);
    }
}
