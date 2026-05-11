<?php

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Unit;

class PropertyWidgets extends Component
{
    // Bed Status Data
    public int $totalBeds = 0;
    public int $occupiedBeds = 0;
    public int $availableBeds = 0;
    public int $occupiedPercent = 0;
    public int $availablePercent = 0;
    public float $occupancyRate = 0.0;

    // Unit + floor counts for reference
    public int $totalUnits = 0;
    public int $totalFloors = 0;

    public ?int $selectedBuildingId = null;

    public function mount($initialBuildingId = null)
    {
        if ($initialBuildingId) {
            $this->selectedBuildingId = $initialBuildingId;
            $this->loadBedStats();
        }
    }

    #[On('buildingSelected')]
    public function onBuildingSelected($buildingId): void
    {
        $this->selectedBuildingId = $buildingId;
        $this->loadBedStats();
    }

    private function loadBedStats()
    {
        $query = Unit::with(['beds.leases' => function($query) {
            $query->where('status', 'Active');
        }]);

        if ($this->selectedBuildingId) {
            $query->where('property_id', $this->selectedBuildingId);
        }

        $units = $query->get();

        $this->totalUnits = $units->count();
        $this->totalFloors = $units->pluck('floor_number')->filter(fn($f) => $f !== null)->unique()->count();

        $totalBeds = 0;
        $occupiedBeds = 0;

        foreach ($units as $unit) {
            foreach ($unit->beds as $bed) {
                $totalBeds++;
                if ($bed->leases->isNotEmpty()) {
                    $occupiedBeds++;
                }
            }
        }

        $this->totalBeds = $totalBeds;
        $this->occupiedBeds = $occupiedBeds;
        $this->availableBeds = $totalBeds - $occupiedBeds;

        if ($this->totalBeds > 0) {
            $this->occupiedPercent = round(($this->occupiedBeds / $this->totalBeds) * 100);
            $this->availablePercent = round(($this->availableBeds / $this->totalBeds) * 100);
            $this->occupancyRate = round(($this->occupiedBeds / $this->totalBeds) * 100, 1);
        } else {
            $this->occupiedPercent = 0;
            $this->availablePercent = 0;
            $this->occupancyRate = 0.0;
        }
    }

    #[On('refresh-property-list')]
    #[On('refresh-unit-list')]
    public function refreshWidgets(): void
    {
        if ($this->selectedBuildingId) {
            $this->loadBedStats();
        }
    }

    public function render()
    {
        return view('livewire.layouts.property-widgets');
    }
}
