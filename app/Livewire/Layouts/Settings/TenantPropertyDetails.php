<?php

namespace App\Livewire\Layouts\Settings;

use App\Models\Lease;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantPropertyDetails extends Component
{
    public $hasLease = false;

    // Property info
    public $buildingName = '';
    public $address = '';
    public $description = '';
    public $photos = [];
    public $documents = [];
    public $activePhotoIndex = 0;

    // Unit info
    public $unit = null;
    public $amenities = [];

    public function mount(): void
    {
        $user = Auth::user();

        $lease = Lease::with(['bed.unit.property.documents'])
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if (!$lease) {
            $lease = Lease::with(['bed.unit.property.documents'])
                ->where('tenant_id', $user->user_id)
                ->where('status', 'Expired')
                ->latest()
                ->first();
        }

        if (!$lease) return;

        $this->hasLease = true;

        $bed = $lease->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;

        if ($property) {
            $this->buildingName = $property->building_name;
            $this->address = $property->address;
            $this->description = $property->prop_description ?? '';

            // Property photos
            $this->photos = $property->documents
                ->where('category', 'property_photo')
                ->map(fn($doc) => [
                    'id' => $doc->id,
                    'url' => asset('storage/' . $doc->file_path),
                    'name' => $doc->original_name,
                ])
                ->values()
                ->toArray();

            // All property documents visible to tenant
            $this->documents = $property->documents
                ->where('category', '!=', 'property_photo')
                ->map(fn($doc) => [
                    'id' => $doc->id,
                    'url' => asset('storage/' . $doc->file_path),
                    'name' => $doc->original_name,
                    'category' => $doc->category,
                ])
                ->values()
                ->toArray();
        }

        if ($unit) {
            $this->unit = [
                'unit_number' => $unit->unit_number,
                'floor_number' => $unit->floor_number,
                'occupants' => $unit->occupants,
                'living_area' => $unit->living_area,
                'furnishing' => $unit->furnishing,
                'bed_type' => $unit->bed_type,
                'room_cap' => $unit->room_cap,
                'price' => $unit->price,
            ];

            if ($unit->amenities) {
                $decoded = json_decode($unit->amenities, true);
                if (is_array($decoded)) {
                    $this->amenities = $decoded;
                }
            }
        }
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
        return view('livewire.layouts.settings.tenant-property-details');
    }
}
