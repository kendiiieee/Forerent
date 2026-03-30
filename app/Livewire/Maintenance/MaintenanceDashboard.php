<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class MaintenanceDashboard extends Component
{
    use WithPagination;

    public $activeTab = 'All'; // Options: All, Pending, Ongoing (shown as On Hold), Completed
    public $selectedRequestId = null;

    // This listener ensures the dashboard updates if other parts of the app change data
    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        // On load, select the first available request so the right side isn't empty
        $firstRequest = DB::table('maintenance_requests')->orderBy('created_at', 'desc')->first();
        if ($firstRequest) {
            $this->selectedRequestId = $firstRequest->id ?? $firstRequest->request_id; // Handling different ID naming conventions
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function selectRequest($id)
    {
        $this->selectedRequestId = $id;
    }

    public function render()
    {
        // 1. Get Counts for Tabs
        $counts = [
            'All' => DB::table('maintenance_requests')->count(),
            'Pending' => DB::table('maintenance_requests')->where('status', 'Pending')->count(),
            'On Hold' => DB::table('maintenance_requests')->where('status', 'Ongoing')->count(),
            'Completed' => DB::table('maintenance_requests')->where('status', 'Completed')->count(),
        ];

        // 2. Build the List Query (Left Column)
        $query = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->select(
                'maintenance_requests.*',
                'units.floor_number', // Assuming unit number/name is constructed or stored
                'beds.bed_number as unit_name', // Using bed/unit identifier
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            );

        if ($this->activeTab !== 'All') {
            if ($this->activeTab === 'On Hold') {
                $query->where('maintenance_requests.status', 'Ongoing');
            } else {
                $query->where('maintenance_requests.status', $this->activeTab);
            }
        }

        $requests = $query->orderBy('maintenance_requests.created_at', 'desc')->paginate(10);

        // 3. Get Details for Selected Item (Right Column)
        $selectedRequest = null;
        if ($this->selectedRequestId) {
            $selectedRequest = DB::table('maintenance_requests')
                ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->join('properties', 'units.property_id', '=', 'properties.property_id') // Join property for building name
                ->join('users', 'leases.tenant_id', '=', 'users.user_id')
                ->where('maintenance_requests.request_id', $this->selectedRequestId)
                ->select(
                    'maintenance_requests.*',
                    'units.floor_number',
                    'beds.bed_number as unit_name',
                    'properties.building_name',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
                )
                ->first();
        }

        return view('livewire.maintenance.maintenance-dashboard', [
            'counts' => $counts,
            'requests' => $requests,
            'selectedDetail' => $selectedRequest
        ]);
    }
}
