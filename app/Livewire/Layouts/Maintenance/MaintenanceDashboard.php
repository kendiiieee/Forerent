<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class MaintenanceDashboard extends Component
{
    use WithPagination;

    public $activeTab = 'All';
    public $selectedRequestId = null;

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        // Select the first request automatically so the right side isn't empty
        $firstRequest = DB::table('maintenance_requests')->orderBy('created_at', 'desc')->first();
        if ($firstRequest) {
            $this->selectedRequestId = $firstRequest->id ?? $firstRequest->request_id;
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
        // 1. Get Counts
        $counts = [
            'All' => DB::table('maintenance_requests')->count(),
            'Pending' => DB::table('maintenance_requests')->where('status', 'Pending')->count(),
            'On Hold' => DB::table('maintenance_requests')->where('status', 'Ongoing')->count(),
            'Completed' => DB::table('maintenance_requests')->where('status', 'Completed')->count(),
        ];

        // 2. Build List Query (Left Column)
        $query = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->select(
                'maintenance_requests.*',
                'units.floor_number',
                'beds.bed_number as unit_name',
                // FIX: Combine first and last name
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
                ->join('properties', 'units.property_id', '=', 'properties.property_id')
                ->join('users', 'leases.tenant_id', '=', 'users.user_id')
                ->where('maintenance_requests.request_id', $this->selectedRequestId)
                ->select(
                    'maintenance_requests.*',
                    'units.floor_number',
                    'beds.bed_number as unit_name',
                    'properties.building_name',
                    // FIX: Combine first and last name here too
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
                )
                ->first();
        }

        // IMPORTANT: The view path must match resources/views/livewire/layouts/maintenance/
        return view('livewire.layouts.maintenance.maintenance-dashboard', [
            'counts' => $counts,
            'requests' => $requests,
            'selectedDetail' => $selectedRequest
        ]);
    }
}
