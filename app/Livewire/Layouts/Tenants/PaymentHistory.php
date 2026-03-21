<?php

namespace App\Livewire\Layouts\Tenants;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentHistory extends Component
{
    use WithPagination;

    public $activeTab = 'all';

    public function updatedActiveTab() { $this->resetPage(); }

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
                    ->where('transactions.category', '=', 'Rent Payment');
            })
            ->where('billings.billing_id', $billingId)
            ->where('leases.tenant_id', Auth::user()->user_id)
            ->select(
                'billings.billing_id',
                'billings.billing_date',
                'billings.to_pay',
                'units.unit_number',
                'properties.building_name',
                'properties.address',
                'tenant.first_name as tenant_first_name',
                'tenant.last_name as tenant_last_name',
                'tenant.contact as tenant_contact',
                'manager.first_name as manager_first_name',
                'manager.last_name as manager_last_name',
                'manager.contact as manager_contact',
                'transactions.transaction_date as txn_date',
                'transactions.reference_number as txn_reference',
            )
            ->first();

        if (!$record) return;

        $billingDate = Carbon::parse($record->billing_date);

        $data = [
            'invoice_no'  => '20250825-' . str_pad($record->billing_id, 3, '0', STR_PAD_LEFT),
            'issued_date' => $billingDate->format('F d, Y'),
            'due_date'    => $billingDate->copy()->addDays(20)->format('F d, Y'),
            'tenant' => [
                'name'     => $record->tenant_first_name . ' ' . $record->tenant_last_name,
                'unit'     => 'Unit ' . $record->unit_number,
                'building' => $record->building_name,
                'address'  => $record->address,
                'contact'  => $record->tenant_contact ?? 'N/A',
            ],
            'payment' => [
                'date_paid'  => $record->txn_date ? Carbon::parse($record->txn_date)->format('F d, Y') : 'Pending',
                'txn_id'     => $record->txn_reference ?? 'Pending',
                'lease_type' => 'Monthly',
                'period'     => $billingDate->format('F Y'),
            ],
            'recipient' => [
                'name'     => $record->manager_first_name . ' ' . $record->manager_last_name,
                'position' => 'Property Manager',
                'contact'  => $record->manager_contact ?? 'N/A',
            ],
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

        $payments = $query->orderBy('billings.billing_date', 'desc')->paginate(10);

        return view('livewire.layouts.tenants.payment-history', [
            'payments' => $payments,
            'counts'   => $counts,
        ]);
    }
}
