<?php

namespace App\Livewire\Layouts\Properties;

use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class PropertyDetails extends Component
{
    public $propertyId = null;

    public $activePhotoIndex = 0;

    // Store as plain arrays/scalars — no Eloquent model hydration issues
    public $buildingName = '';

    public $address = '';

    public $description = '';

    public $unitCount = 0;

    public $photos = [];

    public $documents = [];

    public function mount($buildingId = null)
    {
        $buildingId = $buildingId ?: $this->resolveInitialBuildingId();

        if ($buildingId) {
            $this->loadPropertyData((int) $buildingId);
        }
    }

    #[On('buildingSelected')]
    public function onBuildingSelected($buildingId = null)
    {
        $buildingId = $buildingId ?: $this->resolveInitialBuildingId();

        if (!$buildingId) {
            return;
        }

        if ($this->propertyId == $buildingId) {
            return; // Skip if same building
        }

        $this->loadPropertyData($buildingId);
    }

    private function resolveInitialBuildingId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if ($user->role === 'landlord') {
            return Property::where('owner_id', $user->user_id)
                ->orderBy('property_id')
                ->value('property_id');
        }

        if ($user->role === 'manager') {
            return Property::whereHas('units', function ($query) use ($user) {
                $query->where('manager_id', $user->user_id);
            })->orderBy('property_id')->value('property_id');
        }

        return Property::orderBy('property_id')->value('property_id');
    }

    private function loadPropertyData($id): void
    {
        $this->propertyId = $id;
        $this->activePhotoIndex = 0;

        // Single query: eager load documents + unit count together
        $property = Property::withCount('units')
            ->with('documents')
            ->find($id);

        if (! $property) {
            $this->reset(['buildingName', 'address', 'description', 'unitCount', 'photos', 'documents']);

            return;
        }

        $this->buildingName = $property->building_name;
        $this->address = $property->address;
        $this->description = $property->prop_description ?? '';
        $this->unitCount = $property->units_count;

        // Filter the already-loaded documents collection in memory (no extra queries)
        $this->photos = $property->documents
            ->where('category', 'property_photo')
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'url' => asset('storage/'.$doc->file_path),
                'name' => $doc->original_name,
            ])
            ->values()
            ->toArray();

        $this->documents = $property->documents
            ->where('category', '!=', 'property_photo')
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'url' => asset('storage/'.$doc->file_path),
                'name' => $doc->original_name,
                'category' => $doc->category,
                'visibility' => $doc->visibility,
                'isPrivate' => in_array($doc->category, PropertyDocument::OWNER_MANAGER_CATEGORIES),
            ])
            ->values()
            ->toArray();
    }

    public function setActivePhoto($index)
    {
        $this->activePhotoIndex = $index;
    }

    public function getCategoryLabel($category)
    {
        return match ($category) {
            'business_permit' => 'Business Permit',
            'bir_2303' => 'BIR 2303',
            'inspection_report' => 'Inspection Report',
            'barangay_clearance' => 'Barangay Clearance',
            'occupancy_permit' => 'Occupancy Permit',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    public function render()
    {
        return view('livewire.layouts.properties.property-details');
    }
}
