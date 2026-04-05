<?php

namespace App\Livewire\Layouts\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceStats extends Component
{
    public $totalCost;
    public $newRequests;
    public $pendingRequests;
    public $currentDate;

    public function mount()
    {
        // 1. Total Maintenance Cost: Sum of 'cost' from 'maintenance_logs'
        $this->totalCost = DB::table('maintenance_logs')
            ->whereYear('completion_date', now()->year)
            ->sum('cost');

        // 2. New Requests: Count of requests created in the current month
        $this->newRequests = DB::table('maintenance_requests')
            ->whereYear('maintenance_requests.created_at', Carbon::now()->year)
            ->whereMonth('maintenance_requests.created_at', Carbon::now()->month)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('leases')
                    ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                    ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                    ->whereColumn('leases.lease_id', 'maintenance_requests.lease_id')
                    ->where('units.manager_id', auth()->id());
            })
            ->count();

        // 3. Pending Requests: Count of requests with status 'Pending'
        $this->pendingRequests = DB::table('maintenance_requests')
            ->where('maintenance_requests.status', 'Pending')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('leases')
                    ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                    ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                    ->whereColumn('leases.lease_id', 'maintenance_requests.lease_id')
                    ->where('units.manager_id', auth()->id());
            })
            ->count();

        // 4. Current Date for display
        $this->currentDate = Carbon::now()->format('M d, Y');
    }

    public function render()
    {
        return view('livewire.layouts.dashboard.maintenance-stats');
    }
}
