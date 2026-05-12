<?php

namespace App\Livewire\Layouts\Properties;

use App\Models\Property;
use Illuminate\Support\Facades\Storage;
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
        // Don't independently resolve — wait for buildingSelected event
        // from BuildingCardsSection to ensure consistent selection
        if ($buildingId) {
            $this->loadPropertyData((int) $buildingId);
        }
    }

    #[On('buildingSelected')]
    public function onBuildingSelected($buildingId = null)
    {
        if (! $buildingId) {
            return;
        }

        if ($this->propertyId == $buildingId) {
            return; // Skip if same building
        }

        $this->loadPropertyData($buildingId);
    }

    #[On('refresh-property-list')]
    public function refreshPropertyDetails()
    {
        if ($this->propertyId) {
            $this->loadPropertyData($this->propertyId);
        }
    }

    #[On('refresh-unit-list')]
    public function refreshFromUnitUpdate($buildingId = null): void
    {
        if (! $this->propertyId) {
            return;
        }

        if ($buildingId && (int) $buildingId !== (int) $this->propertyId) {
            return;
        }

        $this->loadPropertyData((int) $this->propertyId);
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
        // Show landlord-uploaded photos first. If none, show seeded photos.
        // Skip photos where the file no longer exists on disk.
        $photoPartition = $property->documents
            ->where('category', 'property_photo')
            ->partition(fn ($d) => ! $d->is_seed);

        $preferredPhotos = $photoPartition[0]->isNotEmpty() ? $photoPartition[0] : $photoPartition[1];

        $this->photos = $preferredPhotos
            ->filter(fn ($doc) => Storage::disk('public')->exists($doc->file_path))
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'url' => route('file.public', ['path' => $doc->file_path]),
                'name' => $doc->original_name,
            ])
            ->values()
            ->toArray();

        $this->documents = $property->documents
            ->where('category', '!=', 'property_photo')
            ->filter(fn ($doc) => Storage::disk('public')->exists($doc->file_path))
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'url' => route('file.public', ['path' => $doc->file_path]),
                'name' => $doc->original_name,
                'category' => $doc->category,
                'visibility' => $doc->visibility,
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
