<?php

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Unit;

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

    // Vacancy Metrics (merged from VacancyMetrics component)
    public int $totalBeds = 0;
    public int $occupiedBeds = 0;
    public int $vacantBeds = 0;
    public int $vacancyPercent = 0;

    public function mount()
    {
        $this->loadUnitStats();
    }

    private function loadUnitStats()
    {
        // Single query for all unit/bed/lease data
        $units = Unit::with(['beds.leases' => function($query) {
            $query->where('status', 'Active');
        }])->get();

        $this->totalUnits = $units->count();

        $occupiedUnits = 0;
        $vacantUnits = 0;
        $moveInReadyUnits = 0;
        $totalBeds = 0;
        $occupiedBeds = 0;

        foreach ($units as $unit) {
            $hasAnyActiveLease = false;
            $allBedsOccupied = true;
            $bedCount = $unit->beds->count();
            $totalBeds += $bedCount;

            if ($bedCount === 0) {
                $vacantUnits++;
                continue;
            }

            foreach ($unit->beds as $bed) {
                if ($bed->leases->isNotEmpty()) {
                    $hasAnyActiveLease = true;
                    $occupiedBeds++;
                } else {
                    $allBedsOccupied = false;
                }
            }

            if ($allBedsOccupied && $hasAnyActiveLease) {
                $occupiedUnits++;
            } elseif ($hasAnyActiveLease) {
                $moveInReadyUnits++;
            } else {
                $vacantUnits++;
            }
        }

        $this->occupied = $occupiedUnits;
        $this->vacant = $vacantUnits;
        $this->moveInReady = $moveInReadyUnits;

        // Bed-level vacancy metrics
        $this->totalBeds = $totalBeds;
        $this->occupiedBeds = $occupiedBeds;
        $this->vacantBeds = $totalBeds - $occupiedBeds;
        $this->vacancyPercent = $totalBeds > 0 ? round(($this->vacantBeds / $totalBeds) * 100) : 0;

        // Unit-level percentages
        if ($this->totalUnits > 0) {
            $this->occupiedPercent = round(($this->occupied / $this->totalUnits) * 100);
            $this->vacantPercent = round(($this->vacant / $this->totalUnits) * 100);
            $this->moveInReadyPercent = round(($this->moveInReady / $this->totalUnits) * 100);

            $this->occupancyRate = round(($this->occupied / $this->totalUnits) * 100, 1);
            $this->availableUnits = $this->vacant + $this->moveInReady;
        }
    }

    #[On('refresh-property-list')]
    #[On('refresh-unit-list')]
    public function refreshWidgets(): void
    {
        $this->loadUnitStats();
    }

    public function render()
    {
        return view('livewire.layouts.property-widgets');
    }
}
