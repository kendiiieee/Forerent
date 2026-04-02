<?php

namespace App\Livewire\Layouts\Properties;

use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuildingCardsSection extends Component
{
    public $properties = [];
    public $selectedBuilding = null;

    public $showAddButton = true;
    public $showAddUnitButton = false;
    public $stacked = false;
    public $title = 'Buildings';
    public $emptyStateTitle = 'No properties found';
    public $emptyStateDescription = 'Get started by adding your first property.';
    public $addButtonEvent = 'openAddPropertyModal_property-dashboard';
    public $addUnitButtonEvent = 'open-add-unit-modal';

    public $eventName = 'buildingSelected';

    public function mount(
        $properties = null,
        $showAddButton = true,
        $showAddUnitButton = false,
        $stacked = false,
        $title = 'Buildings',
        $addButtonEvent = null,
        $addUnitButtonEvent = null,
        $eventName = 'buildingSelected'
    ) {
        if ($properties) {
            // Convert passed Eloquent collection to plain arrays
            $this->properties = collect($properties)->map(fn($p) => [
                'property_id' => $p->property_id ?? $p['property_id'],
                'building_name' => $p->building_name ?? $p['building_name'],
                'address' => $p->address ?? $p['address'],
                'thumbnail' => $p->thumbnail ?? ($p['thumbnail'] ?? null),
            ])->toArray();
        } else {
            $this->properties = $this->loadPropertiesByRole();
        }

        $this->showAddButton = $showAddButton;
        $this->showAddUnitButton = $showAddUnitButton;
        $this->stacked = (bool) $stacked;
        $this->title = $title;
        $this->addButtonEvent = $addButtonEvent ?? 'openAddPropertyModal_property-dashboard';
        $this->addUnitButtonEvent = $addUnitButtonEvent ?? 'open-add-unit-modal';
        $this->eventName = $eventName;

        // Auto-select the first building (event dispatch happens via JS in the view
        // to ensure sibling components are ready to receive it)
        if (!empty($this->properties)) {
            $this->selectedBuilding = $this->properties[0]['property_id'];
        }
    }

    /**
     * 🔥 Role-based property loading
     */
    protected function loadPropertiesByRole(): array
    {
        $user = Auth::user();

        $query = null;

        if ($user->role === 'landlord') {
            $query = Property::query();
        } elseif ($user->role === 'manager') {
            $ownerIds = Property::whereHas('units', function ($q) use ($user) {
                $q->where('manager_id', $user->user_id);
            })->pluck('owner_id')->unique();

            $query = Property::whereIn('owner_id', $ownerIds);
        }

        if (!$query) {
            return [];
        }

        return $query->orderBy('property_id')->get()->map(fn($p) => [
            'property_id' => $p->property_id,
            'building_name' => $p->building_name,
            'address' => $p->address,
            'thumbnail' => $p->thumbnail,
        ])->toArray();
    }
    public function selectBuilding($propertyId)
    {
        $this->selectedBuilding = $propertyId;
        $this->skipRender();
    }

    /**
     * Refresh the property list when a new property is created elsewhere.
     */
    protected function getListeners(): array
    {
        return array_merge(parent::getListeners() ?? [], [
            'refresh-property-list' => 'refreshProperties',
            'propertyCreated' => 'handleNewProperty',
        ]);
    }

    public function refreshProperties(): void
    {
        $this->properties = $this->loadPropertiesByRole();

        // maintain existing selection if still present, otherwise pick first
        if (!empty($this->properties)) {
            $ids = array_column($this->properties, 'property_id');
            if (!in_array($this->selectedBuilding, $ids)) {
                $this->selectedBuilding = $this->properties[0]['property_id'];
            }
            // Re-dispatch so the selected building's details/photos refresh
            $this->dispatch('buildingSelected', buildingId: $this->selectedBuilding);
        } else {
            $this->selectedBuilding = null;
        }
    }

    public function handleNewProperty($propertyId): void
    {
        // reload list then select the newly created property
        $this->refreshProperties();
        $this->selectedBuilding = $propertyId;
        $this->dispatch($this->eventName, buildingId: $propertyId);
    }

    public function render()
    {
        return view('livewire.layouts.properties.building-cards-section');
    }
}
