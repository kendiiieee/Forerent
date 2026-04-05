<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectedMaintenanceRecords extends Component
{
    public $activeTab = 'schedule';

    /**
     * Upcoming / ongoing maintenance requests for this manager's units.
     */
    public function getScheduleHistory(): array
    {
        $managerId = Auth::id();

        return DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->whereIn('maintenance_requests.status', ['Pending', 'Ongoing'])
            ->whereNull('maintenance_requests.deleted_at')
            ->orderBy('maintenance_requests.created_at', 'desc')
            ->limit(10)
            ->select(
                DB::raw("'Unit ' || units.unit_number as unit"),
                'maintenance_requests.category as task',
                'maintenance_requests.expected_completion_date as scheduled_date',
                'maintenance_requests.urgency',
                'maintenance_requests.status'
            )
            ->get()
            ->map(function ($r) {
                return [
                    'unit'           => $r->unit,
                    'task'           => $r->task,
                    'scheduled_date' => $r->scheduled_date
                        ? \Carbon\Carbon::parse($r->scheduled_date)->format('F d, Y')
                        : 'Not set',
                    'urgency'        => $r->urgency,
                    'status'         => $r->status,
                ];
            })
            ->toArray();
    }

    /**
     * Cost breakdown by category from real maintenance logs.
     */
    public function getCostBreakdown(): array
    {
        $managerId = Auth::id();

        return DB::table('maintenance_logs')
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_logs.deleted_at')
            ->whereNull('maintenance_requests.deleted_at')
            ->groupBy('maintenance_requests.category')
            ->select(
                'maintenance_requests.category',
                DB::raw('SUM(maintenance_logs.cost) as total_cost'),
                DB::raw('COUNT(DISTINCT maintenance_requests.request_id) as request_count'),
                DB::raw('MAX(maintenance_logs.created_at) as last_serviced')
            )
            ->orderByDesc('total_cost')
            ->get()
            ->map(function ($r) {
                return [
                    'category'       => $r->category,
                    'provider'       => $r->request_count . ' ' . \Illuminate\Support\Str::plural('request', $r->request_count),
                    'estimated_cost' => round($r->total_cost, 2),
                    'last_serviced'  => $r->last_serviced
                        ? \Carbon\Carbon::parse($r->last_serviced)->format('F d, Y')
                        : 'N/A',
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.projected-maintenance-records', [
            'scheduleHistory' => $this->getScheduleHistory(),
            'costBreakdown'   => $this->getCostBreakdown(),
        ]);
    }
}
