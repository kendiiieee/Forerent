<?php

namespace App\Livewire\Layouts\Units;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Unit;
use App\Models\Bed;

class UnitAccordion extends Component
{
    use WithPagination;

    public $openUnitId = null;
    public $selectedBuildingId = null;
    public $selectedBuildingName = null;
    public $specifications = [];
    public $sortBy = 'newest';

    /**
     * Listen for building selection from property.blade.php
     */
    #[On('buildingSelected')]
    public function loadUnitsForBuilding($buildingId)
    {
        $this->selectedBuildingId = $buildingId;

        // Single query: get building name using pluck (no model hydration)
        $this->selectedBuildingName = \App\Models\Property::where('property_id', $buildingId)
            ->value('building_name');

        $this->resetPage();
        $this->specifications = [];
        $this->openUnitId = null;

        // Sync Alpine openId with server state
        $this->dispatch('unitsReset');
    }

    /**
     * Redirect to add property page
     */
    public function redirectToAddProperty()
    {
        return redirect()->route('properties.create');
    }

    /**
     * Toggle unit accordion
     */
    public function toggleUnit($unitId)
    {
        if ($this->openUnitId === $unitId) {
            $this->openUnitId = null;
            $this->specifications = [];
        } else {
            $this->openUnitId = $unitId;
            $this->loadSpecifications($unitId);
        }
    }

    /**
     * Load specifications from database (used by toggleUnit when clicking accordion)
     */
    public function loadSpecifications($unitId)
    {
        $unit = Unit::with(['property', 'beds.leases' => function ($query) {
            $query->where('status', 'Active');
        }])->find($unitId);

        if (!$unit) {
            $this->specifications = [];
            return;
        }

        $this->buildSpecificationsFromUnit($unit);
    }

    /**
     * Build specifications from an already-loaded unit model (avoids extra query)
     */
    public function buildSpecificationsFromUnit($unit)
    {
        // Calculate occupancy based on active leases
        $occupiedCount = 0;
        foreach ($unit->beds as $bed) {
            if ($bed->leases->isNotEmpty()) {
                $occupiedCount++;
            }
        }

        $totalCapacity = $unit->unit_cap;

        // Process amenities
        $amenities = json_decode($unit->amenities, true) ?? [];
        $utilitySubsidy = in_array('Utility_Subsidy', $amenities) ? 'Yes' : 'No';

        $displayAmenities = [];
        foreach ($amenities as $amenity) {
            if ($amenity !== 'Utility_Subsidy') {
                $displayAmenities[] = ucwords(str_replace('_', ' ', $amenity));
            }
        }

        // Build specifications
        $this->specifications = [
            'room_capacity' => $unit->room_cap ?? 'N/A',
            'unit_capacity' => $unit->unit_cap ?? 'N/A',
            'room_type' => $unit->room_type ?? 'N/A',
            'bed_type' => $unit->bed_type ?? 'N/A',
            'utility_subsidy' => $utilitySubsidy,
            'occupied_unit' => "$occupiedCount of $totalCapacity",
            'occupied_unit_sub' => $unit->occupants ?? 'N/A',
            'base_rate' => '₱ ' . number_format($unit->price, 0, '.', ','),
            'amenities' => $displayAmenities
        ];
    }

    /**
     * Status styling helpers
     */
    public function getStatusTextClass($status)
    {
        return match (strtolower($status)) {
            'occupied' => 'text-red-600',
            'vacant', 'available' => 'text-green-600',
            'maintenance' => 'text-yellow-600',
            default => 'text-gray-600',
        };
    }

    public function getStatusDotClass($status)
    {
        return match (strtolower($status)) {
            'occupied' => 'bg-red-500',
            'vacant', 'available' => 'bg-green-500',
            'maintenance' => 'bg-yellow-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Calculate unit status based on active leases in beds
     */
    public function calculateUnitStatus($unit)
    {
        $hasAnyActiveLease = false;
        $allBedsOccupied = true;
        $totalBeds = $unit->beds->count();

        if ($totalBeds === 0) {
            return 'Vacant';
        }

        foreach ($unit->beds as $bed) {
            if ($bed->leases->isNotEmpty()) {
                $hasAnyActiveLease = true;
            } else {
                $allBedsOccupied = false;
            }
        }

        if ($allBedsOccupied && $hasAnyActiveLease) {
            return 'Occupied';
        } elseif ($hasAnyActiveLease) {
            return 'Available';
        } else {
            return 'Vacant';
        }
    }

    /**
     * Get floor suffix (1st, 2nd, 3rd, etc.)
     */
    public function getFloorSuffix($floorNumber)
    {
        if ($floorNumber % 100 >= 11 && $floorNumber % 100 <= 13) {
            return $floorNumber . 'th';
        }

        return match ($floorNumber % 10) {
            1 => $floorNumber . 'st',
            2 => $floorNumber . 'nd',
            3 => $floorNumber . 'rd',
            default => $floorNumber . 'th',
        };
    }

    public function getUnitAmenities($unit)
    {
        $amenities = [];

        // Map from unit amenities JSON data
        $unitAmenities = json_decode($unit->amenities, true) ?? [];

        // Convert amenity keys to display names
        foreach ($unitAmenities as $amenity) {
            $displayName = ucwords(str_replace('_', ' ', $amenity));
            $amenities[] = $displayName;
        }

        return $amenities;
    }

    public function render()
    {
        if ($this->selectedBuildingId) {
            $query = Unit::where('property_id', $this->selectedBuildingId)
                ->when(Auth::user()->role === 'manager', fn($q) => $q->where('manager_id', Auth::id())) // ← add this
                ->with(['property', 'beds.leases' => function ($query) {
                    $query->where('status', 'Active');
                }]);

            if ($this->sortBy === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $units = $query->paginate(4);

            if ($this->openUnitId === null && $units->isNotEmpty()) {
                $firstUnit = $units->first();
                $this->openUnitId = $firstUnit->unit_id;
                $this->buildSpecificationsFromUnit($firstUnit);
            }
        } else {
            $units = new LengthAwarePaginator([], 0, 4, 1);
        }

        return view('livewire.layouts.units.unit-accordion', [
            'units' => $units
        ]);
    }
}
