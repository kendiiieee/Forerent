<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProjectedMaintenanceCost extends Component
{
    public $buildingData = [];
    public $chartData = [];
    public $chartLabels = [];

    // Configuration: Estimated cost per repair ticket
    private $avgCostPerTicket = 3500;

    public function mount()
    {
        $this->loadRealChartData();
        $this->loadRealBuildingData();
    }

    private function loadRealChartData()
    {
        // 1. Setup empty months (Jan-Dec)
        $monthlyCosts = array_fill(1, 12, 0);

        // 2. Query REAL requests from database
        $requests = DB::table('maintenance_requests')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        // 3. Fill data based on real ticket counts
        foreach ($requests as $req) {
            $monthlyCosts[$req->month] = $req->count * $this->avgCostPerTicket;
        }

        $this->chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->chartData = array_values($monthlyCosts);
    }

    private function loadRealBuildingData()
    {
        // 1. Query costs per building
        $buildings = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->select(
                'properties.building_name',
                DB::raw('COUNT(maintenance_requests.request_id) as ticket_count')
            )
            ->groupBy('properties.building_name')
            ->get();

        $this->buildingData = [];

        // 2. Format for KPI Cards
        foreach ($buildings as $b) {
            $this->buildingData[] = [
                'name' => $b->building_name,
                'cost' => $b->ticket_count * $this->avgCostPerTicket,
                'change' => rand(2, 8), // Placeholder trend (requires last month data comparison)
                'change_type' => rand(0, 1) ? 'higher' : 'lower'
            ];
        }

        // 3. Fallback if database is empty
        if (empty($this->buildingData)) {
            $this->buildingData[] = [
                'name' => 'No Requests Yet',
                'cost' => 0,
                'change' => 0,
                'change_type' => 'stable'
            ];
        }
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.projected-maintenance-cost');
    }
}
