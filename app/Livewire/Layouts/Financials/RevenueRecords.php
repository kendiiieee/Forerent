<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\MaintenanceLog;
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

    public function updatedActiveTab()
    {
        $this->resetPage('paymentPage');
        $this->resetPage('maintenancePage');
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
            ->leftJoin('billings', 'transactions.billing_id', '=', 'billings.billing_id')
            ->leftJoin('leases', 'billings.lease_id', '=', 'leases.lease_id')
            ->leftJoin('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->leftJoin('units', 'beds.unit_id', '=', 'units.unit_id')
            ->leftJoin('properties', 'units.property_id', '=', 'properties.property_id')
            ->select('transactions.*', 'properties.building_name as property_name')
            ->when($this->selectedMonth, function ($query) {
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

        // If the current page is beyond available pages, snap back to last valid page
        if ($paymentHistory->isEmpty() && $this->getPage('paymentPage') > 1) {
            $lastPage = $paymentHistory->lastPage();
            $this->setPage($lastPage ?: 1, 'paymentPage');
            $paymentHistory = Transaction::query()
                ->leftJoin('billings', 'transactions.billing_id', '=', 'billings.billing_id')
                ->leftJoin('leases', 'billings.lease_id', '=', 'leases.lease_id')
                ->leftJoin('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->leftJoin('units', 'beds.unit_id', '=', 'units.unit_id')
                ->leftJoin('properties', 'units.property_id', '=', 'properties.property_id')
                ->select('transactions.*', 'properties.building_name as property_name')
                ->when($this->selectedMonth, function ($query) {
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
        }

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

        // If the current page is beyond available pages, snap back to last valid page
        if ($maintenanceHistory->isEmpty() && $this->getPage('maintenancePage') > 1) {
            $lastPage = $maintenanceHistory->lastPage();
            $this->setPage($lastPage ?: 1, 'maintenancePage');
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
        }

        return view('livewire.layouts.financials.revenue-records', [
            'paymentHistory' => $paymentHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'monthOptions' => $monthOptions,
            'buildingOptions' => $buildingOptions,
        ]);
    }
}
