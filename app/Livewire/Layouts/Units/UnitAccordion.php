<?php

namespace App\Livewire\Layouts\Units;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Unit;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Lease;

class UnitAccordion extends Component
{
    use WithPagination;

    public $openUnitId = null;
    public $selectedBuildingId = null;
    public $selectedBuildingName = null;
    public $specifications = [];
    public $sortBy = 'newest';
    public $search = '';
    public $unitTenants = [];

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

        // Single query: get building name using pluck (no model hydration)
        $this->selectedBuildingName = \App\Models\Property::where('property_id', $buildingId)
            ->value('building_name');

        $this->resetPage();
        $this->specifications = [];
        $this->unitTenants = [];
        $this->openUnitId = null;

        // Sync Alpine openId with server state
        $this->dispatch('unitsReset');
    }

    #[On('refresh-unit-list')]
    public function refreshUnits($buildingId = null, $unitId = null): void
    {
        $targetBuildingId = $buildingId ?: $this->selectedBuildingId;

        if (!$targetBuildingId) {
            return;
        }

        $this->selectedBuildingId = (int) $targetBuildingId;
        $this->selectedBuildingName = Property::where('property_id', $this->selectedBuildingId)
            ->value('building_name');

        $this->resetPage();

        if ($unitId) {
            $this->openUnitId = (int) $unitId;
            $this->loadSpecifications((int) $unitId);
            $this->loadTenantData((int) $unitId);
        } elseif ($this->openUnitId) {
            $this->loadSpecifications((int) $this->openUnitId);
            $this->loadTenantData((int) $this->openUnitId);
        } else {
            $this->specifications = [];
            $this->unitTenants = [];
        }

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
            $this->unitTenants = [];
        } else {
            $this->openUnitId = $unitId;
            $this->loadSpecifications($unitId);
            $this->loadTenantData($unitId);
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
            'unit_capacity' => $unit->room_cap ?? 'N/A',
            'room_type' => ($unit->room_cap ? ($unit->room_cap . '-in-a-Room Bedspace') : 'N/A'),
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
     * Load tenant and contract data for a unit's beds.
     */
    public function loadTenantData($unitId)
    {
        $this->unitTenants = [];

        if (Auth::user()->role !== 'landlord') {
            return;
        }

        $leases = Lease::whereHas('bed', fn($q) => $q->where('unit_id', $unitId))
            ->whereIn('status', ['Active', 'Expired'])
            ->with(['tenant', 'bed', 'moveInInspections', 'moveOutInspections'])
            ->latest()
            ->get()
            ->unique('tenant_id');

        $tenants = [];
        foreach ($leases as $lease) {
            if (!$lease->tenant) continue;

            $tenants[] = [
                'lease_id' => $lease->lease_id,
                'tenant_name' => $lease->tenant->first_name . ' ' . $lease->tenant->last_name,
                'bed_number' => $lease->bed?->bed_number,
                'lease_status' => $lease->status,
                'contract_status' => $lease->contract_status ?? 'draft',
                'start_date' => $lease->start_date?->format('M d, Y'),
                'end_date' => $lease->end_date?->format('M d, Y'),
                'move_in_signed' => (bool) $lease->contract_agreed,
                'move_out_signed' => (bool) $lease->moveout_contract_agreed,
                'has_move_out' => (bool) $lease->move_out_initiated_at,
            ];
        }

        $this->unitTenants = $tenants;
    }

    /**
     * Open the landlord contract viewer modal for a specific lease.
     */
    public function viewContract($leaseId, $contractType = 'move-in')
    {
        $this->dispatch('open-landlord-contract-viewer', leaseId: $leaseId, contractType: $contractType);
    }

    /**
     * Status styling helpers
     */
    public function getStatusTextClass($status)
    {
        return match (strtolower($status)) {
            'occupied' => 'text-red-600',
            'partially occupied' => 'text-orange-600',
            'available' => 'text-green-600',
            'maintenance' => 'text-yellow-600',
            default => 'text-gray-600',
        };
    }

    public function getStatusDotClass($status)
    {
        return match (strtolower($status)) {
            'occupied' => 'bg-red-500',
            'partially occupied' => 'bg-orange-500',
            'available' => 'bg-green-500',
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
            return 'Partially Occupied';
        } else {
            return 'Available';
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

    public function updatedSearch()
    {
        $this->resetPage();
        $this->openUnitId = null;
        $this->specifications = [];
        $this->unitTenants = [];
    }

    public function render()
    {
        $suggestions = [];

        if ($this->selectedBuildingId) {
            $query = Unit::where('property_id', $this->selectedBuildingId)
                ->when(Auth::user()->role === 'manager', fn($q) => $q->where('manager_id', Auth::id()))
                ->with(['property', 'beds.leases' => function ($query) {
                    $query->where('status', 'Active');
                }]);

            // Build suggestions from unfiltered units
            $allUnits = (clone $query)->get();
            $suggestions = $allUnits->flatMap(function ($unit) {
                $status = $this->calculateUnitStatus($unit);
                $floor = $this->getFloorSuffix($unit->floor_number ?? 1);
                return [
                    'Unit #' . $unit->unit_number,
                    $status,
                    $floor . ' Floor',
                    ($unit->room_cap ? ($unit->room_cap . '-in-a-Room Bedspace') : null),
                ];
            })->filter()->unique()->values()->toArray();

            // Apply search filter
            if (!empty($this->search)) {
                $term = $this->search;
                // Strip common prefixes for matching
                $cleanTerm = preg_replace('/^(Unit\s*#?\s*)/i', '', $term);

                $query->where(function ($q) use ($term, $cleanTerm) {
                    $search = '%' . $term . '%';
                    $cleanSearch = '%' . $cleanTerm . '%';
                    $q->where('unit_number', 'like', $cleanSearch)
                      ->orWhere('floor_number', 'like', $cleanSearch);
                });
            }

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
                $this->loadTenantData($firstUnit->unit_id);
            }
        } else {
            $units = new LengthAwarePaginator([], 0, 4, 1);
        }

        return view('livewire.layouts.units.unit-accordion', [
            'units' => $units,
            'suggestions' => $suggestions,
        ]);
    }
}
