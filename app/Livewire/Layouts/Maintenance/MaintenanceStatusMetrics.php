<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceStatusMetrics extends Component
{
    public int $activeMaintenance = 0;
    public int $pendingRequests = 0;
    public int $avgTurnaroundDays = 0;

    public function mount(): void
    {
        $managerId = Auth::id();

        // Active = Ongoing requests for this manager's units
        $this->activeMaintenance = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->where('maintenance_requests.status', 'Ongoing')
            ->whereNull('maintenance_requests.deleted_at')
            ->count();

        // Pending requests
        $this->pendingRequests = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->where('maintenance_requests.status', 'Pending')
            ->whereNull('maintenance_requests.deleted_at')
            ->count();

        // Average turnaround = avg days between created_at and updated_at for completed requests
        $avg = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->where('maintenance_requests.status', 'Completed')
            ->whereNull('maintenance_requests.deleted_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (maintenance_requests.updated_at - maintenance_requests.created_at)) / 86400) as avg_days')
            ->value('avg_days');

        $this->avgTurnaroundDays = $avg ? (int) round($avg) : 0;
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.maintenance-status-metrics');
    }
}
