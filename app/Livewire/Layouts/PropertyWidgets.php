<?php

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Unit;
use App\Models\Bed;

class PropertyWidgets extends Component
{
    // Unit Status Data
    public int $totalUnits = 0;
    public int $occupied = 0;
    public int $occupiedPercent = 0;
    public int $vacant = 0;
    public int $vacantPercent = 0;
    public int $moveInReady = 0;
    public int $moveInReadyPercent = 0;
    public float $occupancyRate = 0.0;
    public int $availableUnits = 0;

    // Donut chart data
    public array $unitStatusData = [];

    public function mount()
    {
        $this->loadUnitStats();
    }

    private function loadUnitStats()
    {
        // Calculate unit status based on active leases in beds
        $this->totalUnits = Unit::count();

        // Get units with their beds and active leases
        $units = Unit::with(['beds.leases' => function($query) {
            $query->where('status', 'Active');
        }])->get();

        $occupiedUnits = 0;
        $vacantUnits = 0;
        $moveInReadyUnits = 0;

        foreach ($units as $unit) {
            $hasAnyActiveLease = false;
            $allBedsOccupied = true;
            $totalBeds = $unit->beds->count();
            
            if ($totalBeds === 0) {
                $vacantUnits++;
                continue;
            }

            foreach ($unit->beds as $bed) {
                if ($bed->leases->isNotEmpty()) {
                    $hasAnyActiveLease = true;
                } else {
                    $allBedsOccupied = false;
                }
            }

            if ($allBedsOccupied && $hasAnyActiveLease) {
                // All beds have active leases
                $occupiedUnits++;
            } elseif ($hasAnyActiveLease) {
                // Some beds have active leases - move-in ready
                $moveInReadyUnits++;
            } else {
                // No active leases at all - fully vacant
                $vacantUnits++;
            }
        }

        $this->occupied = $occupiedUnits;
        $this->vacant = $vacantUnits;
        $this->moveInReady = $moveInReadyUnits;

        // Calculate percentages
        if ($this->totalUnits > 0) {
            $this->occupiedPercent = round(($this->occupied / $this->totalUnits) * 100);
            $this->vacantPercent = round(($this->vacant / $this->totalUnits) * 100);
            $this->moveInReadyPercent = round(($this->moveInReady / $this->totalUnits) * 100);

            $this->occupancyRate = round(($this->occupied / $this->totalUnits) * 100, 1);
            $this->availableUnits = $this->vacant + $this->moveInReady;
        }

        // Prepare data for donut chart
        $this->unitStatusData = [
            ['label' => 'Occupied', 'value' => $this->occupiedPercent, 'count' => $this->occupied],
            ['label' => 'Available', 'value' => $this->moveInReadyPercent, 'count' => $this->moveInReady],
            ['label' => 'Vacant', 'value' => $this->vacantPercent, 'count' => $this->vacant],
        ];
    }

    public function getUnitStatusChartData()
    {
        return [
            ['label' => 'Occupied', 'value' => $this->occupiedPercent, 'count' => $this->occupied],
            ['label' => 'Available', 'value' => $this->moveInReadyPercent, 'count' => $this->moveInReady],
            ['label' => 'Vacant', 'value' => $this->vacantPercent, 'count' => $this->vacant],
        ];
    }

    #[On('refresh-property-list')]
    #[On('refresh-unit-list')]
    public function refreshWidgets(): void
    {
        $this->loadUnitStats();
    }

    public function render()
    {
        return view('livewire.layouts.property-widgets', [
            'unitStatusData' => $this->getUnitStatusChartData()
        ]);
    }
}
