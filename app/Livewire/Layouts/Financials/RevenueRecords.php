<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\MaintenanceLog;
use App\Models\BillingItem;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueRecords extends Component
{
    use WithPagination;

    public $activeTab = 'payment';
    public $selectedMonth = null;
    public $selectedBuilding = null;
    public $search = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
    }

    // Reset pagination when filters change
    public function updatedSelectedMonth()
    {
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
    }

    public function updatedSelectedBuilding()
    {
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
    }

    public function updatedSearch()
    {
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
    }

    public function updatedActiveTab()
    {
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
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
            ->where('billings.billing_id', $billingId)
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
                'manager.contact as manager_contact'
            )
            ->first();

        if (!$record) return;

        $txn = Transaction::where('billing_id', $billingId)
            ->whereIn('category', ['Rent Payment', 'Advance', 'Deposit'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->first();

        $billingDate = Carbon::parse($record->billing_date);
        $dueDate = $record->due_date
            ? Carbon::parse($record->due_date)->format('F d, Y')
            : $billingDate->copy()->addDays(20)->format('F d, Y');

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
                'date_paid'       => $txn?->transaction_date ? Carbon::parse($txn->transaction_date)->format('F d, Y') : 'Pending',
                'payment_method'  => $txn?->payment_method ?? 'Pending',
                'txn_id'          => $txn?->payment_method
                    ? ['GCash' => 'GC', 'Maya' => 'MY', 'Bank Transfer' => 'BT', 'Cash' => 'CS'][$txn->payment_method] . '-' . mt_rand(1000000000, 9999999999)
                    : 'Pending',
                'reference_no'    => $txn?->reference_number ?? 'Pending',
                'or_number'       => $txn?->or_number ?? 'Pending',
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

    public function render()
    {
        $search = trim((string) $this->search);

        $monthOptions = [
            'january' => 'January', 'february' => 'February', 'march' => 'March',
            'april' => 'April', 'may' => 'May', 'june' => 'June',
            'july' => 'July', 'august' => 'August', 'september' => 'September',
            'october' => 'October', 'november' => 'November', 'december' => 'December',
        ];


        $buildingOptions = [];
        try {
            $buildingOptions = Property::distinct()
                ->pluck('building_name', 'building_name')
                ->toArray();
        } catch (\Exception $e) {
            $buildingOptions = [];
        }


        $paymentHistory = Transaction::query()
            ->join('billings', 'transactions.billing_id', '=', 'billings.billing_id')
            ->join('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->whereNotNull('transactions.billing_id')
            ->select('transactions.*', 'properties.building_name as property_name')
            ->when($this->selectedMonth, function ($query) {
                // Convert "january" to 1
                $monthNumber = Carbon::parse($this->selectedMonth)->month;
                $query->whereMonth('transactions.transaction_date', $monthNumber);
            })
            ->when($this->selectedBuilding, function ($query) {
                $query->where('properties.building_name', $this->selectedBuilding);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('transactions.name', 'like', "%{$search}%")
                        ->orWhere('transactions.reference_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('transactions.transaction_date', 'desc')
            ->paginate(10, ['*'], 'paymentPage');

        $maintenanceHistory = MaintenanceLog::query()
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->select(
                'maintenance_logs.*',
                'maintenance_requests.problem',
                'beds.bed_number as unit_number',
                'properties.building_name as property_name',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            )
            ->when($this->selectedMonth, function ($query) {
                $monthNumber = Carbon::parse($this->selectedMonth)->month;
                $query->whereMonth('maintenance_logs.completion_date', $monthNumber);
            })
            ->when($this->selectedBuilding, function ($query) {
                $query->where('properties.building_name', $this->selectedBuilding);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereRaw("CONCAT(users.first_name, ' ', users.last_name) like ?", ["%{$search}%"])
                        ->orWhere('maintenance_requests.problem', 'like', "%{$search}%");
                });
            })
            ->orderBy('maintenance_logs.completion_date', 'desc')
            ->paginate(10, ['*'], 'maintenancePage');

        return view('livewire.layouts.financials.revenue-records', [
            'paymentHistory' => $paymentHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'monthOptions' => $monthOptions,
            'buildingOptions' => $buildingOptions,
        ]);
    }
}
