<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\BillingItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentHistory extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $search = '';

    public function updatedActiveTab() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

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

        $payments = $query->orderBy('billings.billing_date', 'desc')->paginate(10);

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
