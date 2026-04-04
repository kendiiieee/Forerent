<?php

namespace App\Livewire\Layouts\Managers;

use App\Models\Unit;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class ManagerDetail extends Component
{
    public $currentManagerId = null;
    public $currentManager = null;
    public $buildings = [];
    public $units = [];
    public $beds = [];

    public $selectedBuildingId = null;
    public $totalBuildings = 0;
    public $totalUnits = 0;


    public function mount(?int $initialManagerId = null): void
    {
        if ($initialManagerId) {
            $this->loadManager($initialManagerId);
        }
    }

    #[On('managerSelected')]
    public function loadManager(?int $managerId): void
    {
        if (!$managerId) {
            $this->resetManagerData();
            return;
        }

        $this->currentManagerId = $managerId;
        $this->currentManager = User::where('user_id', $managerId)->first();

        if (!$this->currentManager) {
            $this->resetManagerData();
            return;
        }

        $this->buildings = $this->getBuildingsManaged($managerId);
        $this->totalBuildings = count($this->buildings);

        // Count ALL units across all buildings before filtering
        $this->totalUnits = $this->getManagedUnits($managerId)->count();

        // Auto-select the first building
        $firstBuilding = is_array($this->buildings)
            ? reset($this->buildings)
            : $this->buildings->first();

        $this->selectedBuildingId = $firstBuilding?->property_id ?? null;

        // Load units filtered to the selected building
        $this->units = $this->getManagedUnits($managerId, $this->selectedBuildingId);
    }

    #[On('managerUpdated')]
    public function refreshManagerData($managerId): void
    {
        $this->loadManager($managerId);
    }

    #[On('managerModalClosed')]
    public function refreshOnModalClose(): void
    {
        if ($this->currentManagerId) {
            $this->loadManager($this->currentManagerId);
        }
    }

    public function selectBuilding(int $buildingId): void
    {
        if (!$this->currentManagerId) {
            return;
        }

        $this->selectedBuildingId = $buildingId;
        $this->units = $this->getManagedUnits($this->currentManagerId, $buildingId);
    }

    public function editManager(): void
    {
        if ($this->currentManagerId) {
            $this->dispatch(
                'openManagerModal_manager-dashboard',
                managerId: $this->currentManagerId
            );
        }
    }

    private function resetManagerData(): void
    {
        $this->currentManagerId = null;
        $this->currentManager = null;
        $this->units = [];
        $this->buildings = [];
        $this->totalBuildings = 0;
        $this->totalUnits = 0;
        $this->selectedBuildingId = null;
    }

    private function getManagedUnits(int $managerId, ?int $propertyId = null)
    {
        $query = Unit::where('manager_id', $managerId)
            ->with(['property', 'beds'])
            ->whereHas('property', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->orderBy('unit_number')
            ->select('units.*');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        return $query->get()->each(function ($unit) {
            $unit->total_beds = $unit->beds->count();
            $unit->available_beds = $unit->beds->where('status', 'Vacant')->count();
            $unit->status = $unit->available_beds === 0 ? 'Full' : 'Vacant';

            if (!isset($unit->bed_type) || empty($unit->bed_type)) {
                $unit->bed_type = $unit->beds->first()?->bed_type ?? 'N/A';
            }
        });
    }

    private function getBuildingsManaged(int $managerId)
    {
        $propertyIds = Unit::where('manager_id', $managerId)
            ->whereHas('property', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->pluck('property_id')
            ->unique()
            ->values();

        return Property::whereIn('property_id', $propertyIds)->get();
    }

    public function render()
    {
        return view('livewire.layouts.managers.manager-detail');
    }
}
