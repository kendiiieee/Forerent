<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectedMaintenanceCost extends Component
{
    public $buildingData = [];
    public $chartData = [];
    public $chartLabels = [];

    public function mount()
    {
        $this->loadRealChartData();
        $this->loadRealBuildingData();
    }

    private function loadRealChartData()
    {
        $managerId = Auth::id();
        $currentYear = (int) date('Y');
        $monthlyCosts = array_fill(1, 12, 0);

        // Query real costs from maintenance_logs grouped by month
        $rows = DB::table('maintenance_logs')
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_logs.deleted_at')
            ->whereNull('maintenance_requests.deleted_at')
            ->whereYear('maintenance_logs.created_at', $currentYear)
            ->select(
                DB::raw('EXTRACT(MONTH FROM maintenance_logs.created_at)::int as month'),
                DB::raw('SUM(maintenance_logs.cost) as total')
            )
            ->groupBy('month')
            ->get();

        foreach ($rows as $row) {
            $monthlyCosts[$row->month] = round($row->total, 2);
        }

        $this->chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->chartData = array_values($monthlyCosts);
    }

    private function loadRealBuildingData()
    {
        $managerId = Auth::id();
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        // Get current month costs per building
        $buildings = DB::table('maintenance_logs')
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_logs.deleted_at')
            ->whereNull('maintenance_requests.deleted_at')
            ->select(
                'properties.building_name',
                DB::raw('SUM(maintenance_logs.cost) as total_cost')
            )
            ->groupBy('properties.building_name')
            ->orderByDesc('total_cost')
            ->get();

        // Get last month costs for trend comparison
        $lastMonthCosts = DB::table('maintenance_logs')
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_logs.deleted_at')
            ->whereNull('maintenance_requests.deleted_at')
            ->whereYear('maintenance_logs.created_at', $currentMonth === 1 ? $currentYear - 1 : $currentYear)
            ->whereMonth('maintenance_logs.created_at', $currentMonth === 1 ? 12 : $currentMonth - 1)
            ->select('properties.building_name', DB::raw('SUM(maintenance_logs.cost) as total_cost'))
            ->groupBy('properties.building_name')
            ->pluck('total_cost', 'properties.building_name');

        $this->buildingData = [];

        foreach ($buildings as $b) {
            $lastMonth = $lastMonthCosts[$b->building_name] ?? 0;
            $change = 0;
            $changeType = 'stable';

            if ($lastMonth > 0) {
                $change = round(abs($b->total_cost - $lastMonth) / $lastMonth * 100);
                $changeType = $b->total_cost > $lastMonth ? 'higher' : 'lower';
            } elseif ($b->total_cost > 0) {
                $change = 100;
                $changeType = 'higher';
            }

            $this->buildingData[] = [
                'name'        => $b->building_name,
                'cost'        => round($b->total_cost, 2),
                'change'      => $change,
                'change_type' => $changeType,
            ];
        }

        if (empty($this->buildingData)) {
            $this->buildingData[] = [
                'name'        => 'No Costs Recorded',
                'cost'        => 0,
                'change'      => 0,
                'change_type' => 'stable',
            ];
        }
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.projected-maintenance-cost');
    }
}
