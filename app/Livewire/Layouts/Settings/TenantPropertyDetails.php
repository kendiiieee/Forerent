<?php

namespace App\Livewire\Layouts\Settings;

use App\Models\Lease;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;

class TenantPropertyDetails extends Component
{
    public bool $hasLease = false;

    // Property info
    public string $buildingName = '';

    public string $address = '';

    public string $description = '';

    public array $photos = [];

    public array $documents = [];

    public int $activePhotoIndex = 0;

    // Unit info
    public ?array $unit = null;

    public array $amenities = [];

    // Tenant government ID
    public int|string|null $tenantGovernmentId = null;

    public function mount(): void
    {
        $user = Auth::user();

        $lease = Lease::with(['bed.unit.property.documents'])
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if (! $lease) {
            $lease = Lease::with(['bed.unit.property.documents'])
                ->where('tenant_id', $user->user_id)
                ->where('status', 'Expired')
                ->latest()
                ->first();
        }

        if (! $lease) {
            return;
        }

        $this->hasLease = true;

        $bed = $lease->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;

        if ($property) {
            $this->buildingName = $property->building_name;
            $this->address = $property->address;
            $this->description = $property->prop_description ?? '';

            // Property photos
            // Prefer landlord-uploaded photos (non-seed) first, fallback to seeded photos
            $photoPartition = $property->documents
                ->where('category', 'property_photo')
                ->partition(fn ($d) => ! $d->is_seed);

            $preferredPhotos = $photoPartition[0]->isNotEmpty() ? $photoPartition[0] : $photoPartition[1];

            $this->photos = $preferredPhotos->filter(fn ($doc) => Storage::disk('public')->exists($doc->file_path))->map(fn ($doc) => [
                'id' => $doc->id,
                'url' => route('file.public', ['path' => $doc->file_path]),
                'name' => $doc->original_name,
            ])
                ->values()
                ->toArray();

            // Show all property documents uploaded by landlord/manager.
            // Skip documents where the file no longer exists on disk.
            $this->documents = $property->documents
                ->where('category', '!=', 'property_photo')
                ->filter(fn ($doc) => Storage::disk('public')->exists($doc->file_path))
                ->map(fn ($doc) => [
                    'id' => $doc->id,
                    'url' => route('file.public', ['path' => $doc->file_path]),
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

    public function setActivePhoto(int $index): void
    {
        $this->activePhotoIndex = $index;
    }

    public function getCategoryLabel(string $category): string
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

    public function render(): View
    {
        return view('livewire.layouts.settings.tenant-property-details');
    }
}
