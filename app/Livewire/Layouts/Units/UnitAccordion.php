<?php

namespace App\Livewire\Layouts\Units;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Unit;
use App\Models\Bed;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class UnitAccordion extends Component
{
    use WithPagination;

    public $openUnitId = null;
    public $selectedBuildingId = null;
    public $specifications = [];
    public $hoveredUnitId = null;
    public $showAddButton = true;

    public function mount($showAddButton = true): void
    {
        $this->showAddButton = (bool) $showAddButton;

        if ($this->selectedBuildingId) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $defaultPropertyId = null;
        if ($user->role === 'landlord') {
            $defaultPropertyId = Property::query()->orderBy('property_id')->value('property_id');
        } elseif ($user->role === 'manager') {
            $defaultPropertyId = Property::query()
                ->whereHas('units', function ($query) use ($user) {
                    $query->where('manager_id', $user->user_id);
                })
                ->orderBy('property_id')
                ->value('property_id');
        }

        if ($defaultPropertyId) {
            $this->loadUnitsForBuilding($defaultPropertyId);
        }
    }

    /**
     * Listen for building selection from property.blade.php
     */
    #[On('buildingSelected')]
    #[On('property-selected')]
    public function loadUnitsForBuilding($buildingId = null, $id = null)
    {
        $buildingId = $buildingId ?? $id;
        if (!$buildingId) {
            return;
        }

        $this->selectedBuildingId = $buildingId;
        $this->resetPage();
        $this->specifications = [];
        $this->openUnitId = null;
        $this->hoveredUnitId = null;

        // Auto-open the first unit if available
        $firstUnit = Unit::where('property_id', $this->selectedBuildingId)->first();
        if ($firstUnit) {
            $this->openUnitId = $firstUnit->unit_id;
            $this->loadSpecifications($this->openUnitId);
        }
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
        $this->hoveredUnitId = null;
    }

    /**
     * Set hover state
     */
    public function setHover($unitId)
    {
        $this->hoveredUnitId = $unitId;
    }

    /**
     * Clear hover state
     */
    public function clearHover()
    {
        $this->hoveredUnitId = null;
    }

    /**
     * Load specifications from database
     */
    public function loadSpecifications($unitId)
    {
        $unit = Unit::with(['property', 'beds.leases' => function($query) {
            $query->where('status', 'Active');
        }])->find($unitId);

        if (!$unit) {
            $this->specifications = [];
            return;
        }

        // Calculate occupancy based on active leases
        $occupiedCount = 0;
        foreach ($unit->beds as $bed) {
            if ($bed->leases->isNotEmpty()) {
                $occupiedCount++;
            }
        }
        
        $totalCapacity = $unit->room_cap;

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
            'bed_type' => $unit->bed_type ?? 'N/A',
            'furnishing' => $unit->furnishing ?? 'N/A',
            'living_area' => ($unit->living_area ? $unit->living_area . ' sqft' : 'N/A'),
            'occupants' => $unit->occupants ?? 'N/A',
            'utility_subsidy' => $utilitySubsidy,
            'occupied_unit' => "$occupiedCount of $totalCapacity",
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
            $units = Unit::where('property_id', $this->selectedBuildingId)
                ->with(['property', 'beds.leases' => function($query) {
                    $query->where('status', 'Active');
                }])
                ->paginate(4);
        } else {
            $units = new LengthAwarePaginator([], 0, 4, 1);
        }

        return view('livewire.layouts.units.unit-accordion', [
            'units' => $units
        ]);
    }
}
