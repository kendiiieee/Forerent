<?php

namespace App\Livewire\Layouts\Properties;

use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuildingCardsSection extends Component
{
    public $properties;
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
        $this->properties = $properties ?? $this->loadPropertiesByRole();
        $this->showAddButton = $showAddButton;
        $this->showAddUnitButton = $showAddUnitButton;
        $this->stacked = (bool) $stacked;
        $this->title = $title;
        $this->addButtonEvent = $addButtonEvent ?? 'openAddPropertyModal_property-dashboard';
        $this->addUnitButtonEvent = $addUnitButtonEvent ?? 'open-add-unit-modal';
        $this->eventName = $eventName;

        // Auto-select first building and notify listeners so units load on first render.
        if ($this->properties->isNotEmpty()) {
            $this->selectedBuilding = $this->properties->first()->property_id;
            $this->dispatch($this->eventName, buildingId: $this->selectedBuilding);
        }
    }

    /**
     * 🔥 Role-based property loading
     */
    protected function loadPropertiesByRole()
    {
        $user = Auth::user();

        if ($user->role === 'landlord') {
            return Property::with(['owner', 'units'])->get();
        }

        if ($user->role === 'manager') {
            return Property::whereHas('units', function ($query) use ($user) {
                $query->where('manager_id', $user->user_id); // 👈 not $user->id
            })
                ->with([
                    'owner',
                    'units' => function ($query) use ($user) {
                        $query->where('manager_id', $user->user_id); // 👈 not $user->id
                    }
                ])
                ->get();
        }

        return collect();
    }
    public function selectBuilding($propertyId)
    {
        $this->selectedBuilding = $propertyId;

        $this->dispatch($this->eventName, buildingId: $propertyId);
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
        if ($this->properties->isNotEmpty()) {
            if (!$this->properties->pluck('property_id')->contains($this->selectedBuilding)) {
                $this->selectedBuilding = $this->properties->first()->property_id;
                $this->dispatch($this->eventName, buildingId: $this->selectedBuilding);
            }
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
