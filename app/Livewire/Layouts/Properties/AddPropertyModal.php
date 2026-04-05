<?php

namespace App\Livewire\Layouts\Properties;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class AddPropertyModal extends Component
{
    use WithNotifications, WithFileUploads;

    /** Modal visibility */
    public $isOpen = false;

    /** Unique modal instance */
    public $modalId;

    /** Editing state */
    public $editingPropertyId = null;

    /** Form fields */
    #[Validate('required|string|max:255')]
    public $buildingName = '';

    #[Validate('required|string')]
    public $address = '';

    #[Validate('required|string')]
    public $description = '';

    /** File uploads */
    public $propertyPhotos = [];
    public $newPhotos = [];
    public $businessPermit = null;
    public $bir2303 = null;
    public $inspectionReport = null;
    public $barangayClearance = null;
    public $occupancyPermit = null;

    /** Existing documents for edit mode */
    public $existingPhotos = [];
    public $existingDocuments = [];
    public $removedDocumentIds = [];

    public function mount($modalId = null)
    {
        $this->modalId = $modalId ?? uniqid('add_property_modal_');
    }

    protected function getListeners(): array
    {
        return [
            "openAddPropertyModal_{$this->modalId}" => 'open',
            'editProperty' => 'loadPropertyForEditing',
        ];
    }

    protected function rules()
    {
        $hasExistingPhotos = count($this->existingPhotos) > 0;
        $photoRule = $hasExistingPhotos ? 'nullable' : 'required';

        $documentFields = ['businessPermit', 'bir2303', 'inspectionReport', 'barangayClearance', 'occupancyPermit'];
        $categoryMap = [
            'businessPermit' => 'business_permit',
            'bir2303' => 'bir_2303',
            'inspectionReport' => 'inspection_report',
            'barangayClearance' => 'barangay_clearance',
            'occupancyPermit' => 'occupancy_permit',
        ];

        $rules = [
            'buildingName' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'required|string',
            'propertyPhotos' => $photoRule . '|array|min:1',
            'propertyPhotos.*' => 'image|max:10240',
            'newPhotos.*' => 'nullable|image|max:10240',
        ];

        foreach ($documentFields as $field) {
            $hasExisting = collect($this->existingDocuments)->contains('category', $categoryMap[$field]);
            $docRule = $hasExisting ? 'nullable' : 'required';
            $rules[$field] = $docRule . '|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'buildingName.required' => 'Property name is required.',
            'address.required' => 'Address is required.',
            'description.required' => 'Description is required.',
            'propertyPhotos.required' => 'At least one property photo is required.',
            'propertyPhotos.min' => 'At least one property photo is required.',
            'propertyPhotos.*.max' => 'Each photo must be under 10MB.',
            'propertyPhotos.*.image' => 'Only image files are allowed.',
            'businessPermit.required' => 'Business Permit is required.',
            'businessPermit.mimes' => 'Only PDF, JPG, PNG files are allowed.',
            'businessPermit.max' => 'File must be under 10MB.',
            'bir2303.required' => 'BIR 2303 is required.',
            'bir2303.mimes' => 'Only PDF, JPG, PNG files are allowed.',
            'bir2303.max' => 'File must be under 10MB.',
            'inspectionReport.required' => 'Inspection Report is required.',
            'inspectionReport.mimes' => 'Only PDF, JPG, PNG files are allowed.',
            'inspectionReport.max' => 'File must be under 10MB.',
            'barangayClearance.required' => 'Barangay Clearance is required.',
            'barangayClearance.mimes' => 'Only PDF, JPG, PNG files are allowed.',
            'barangayClearance.max' => 'File must be under 10MB.',
            'occupancyPermit.required' => 'Occupancy Permit is required.',
            'occupancyPermit.mimes' => 'Only PDF, JPG, PNG files are allowed.',
            'occupancyPermit.max' => 'File must be under 10MB.',
        ];
    }

    public function open(): void
    {
        $this->resetForm();
        $this->editingPropertyId = null;
        $this->isOpen = true;
    }

    public function loadPropertyForEditing($propertyId): void
    {
        $this->resetForm();
        $property = Property::with('documents')->find($propertyId);

        if ($property) {
            $this->editingPropertyId = $property->property_id;
            $this->buildingName = $property->building_name;
            $this->address = $property->address;
            $this->description = $property->prop_description;

            $this->existingPhotos = $property->documents
                ->where('category', 'property_photo')
                ->map(fn($doc) => [
                    'id' => $doc->id,
                    'url' => Storage::url($doc->file_path),
                    'name' => $doc->original_name,
                ])->values()->toArray();

            $this->existingDocuments = $property->documents
                ->where('category', '!=', 'property_photo')
                ->map(fn($doc) => [
                    'id' => $doc->id,
                    'category' => $doc->category,
                    'name' => $doc->original_name,
                    'url' => Storage::url($doc->file_path),
                ])->values()->toArray();

            $this->isOpen = true;
        }
    }

    public function updatedBusinessPermit(): void { $this->resetValidation('businessPermit'); }
    public function updatedBir2303(): void { $this->resetValidation('bir2303'); }
    public function updatedInspectionReport(): void { $this->resetValidation('inspectionReport'); }
    public function updatedBarangayClearance(): void { $this->resetValidation('barangayClearance'); }
    public function updatedOccupancyPermit(): void { $this->resetValidation('occupancyPermit'); }

    public function updatedNewPhotos(): void
    {
        $remaining = 5 - count($this->propertyPhotos) - count($this->existingPhotos);
        if ($remaining > 0 && !empty($this->newPhotos)) {
            $toAdd = array_slice($this->newPhotos, 0, $remaining);
            $this->propertyPhotos = array_merge($this->propertyPhotos, $toAdd);
        }
        $this->newPhotos = [];
    }

    public function removePhoto($index): void
    {
        if (isset($this->propertyPhotos[$index])) {
            array_splice($this->propertyPhotos, $index, 1);
            $this->propertyPhotos = array_values($this->propertyPhotos);
        }
    }

    public function removeExistingPhoto($docId): void
    {
        $this->removedDocumentIds[] = $docId;
        $this->existingPhotos = array_values(
            array_filter($this->existingPhotos, fn($p) => $p['id'] !== $docId)
        );
    }

    public function removeExistingDocument($docId): void
    {
        $this->removedDocumentIds[] = $docId;
        $this->existingDocuments = array_values(
            array_filter($this->existingDocuments, fn($d) => $d['id'] !== $docId)
        );
    }

    public function close(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->isOpen = false;
        $this->dispatch('propertyModalClosed');
    }

    public function validateAndConfirm(): void
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

        // Validation passed — open the confirmation modal
        $this->dispatch('open-modal', 'save-property-confirmation');
    }

    public function next(): void
    {

        try {
            DB::beginTransaction();

            if ($this->editingPropertyId) {
                $property = Property::find($this->editingPropertyId);
                if ($property) {
                    $property->update([
                        'building_name' => $this->buildingName,
                        'address' => $this->address,
                        'prop_description' => $this->description,
                    ]);

                    // Remove documents marked for deletion
                    if (!empty($this->removedDocumentIds)) {
                        $docsToRemove = PropertyDocument::whereIn('id', $this->removedDocumentIds)->get();
                        foreach ($docsToRemove as $doc) {
                            Storage::disk('public')->delete($doc->file_path);
                            $doc->delete();
                        }
                    }

                    $this->storeFiles($property);

                    $this->notifySuccess(
                        'Property Updated Successfully!',
                        $this->buildingName . ' has been updated.'
                    );
                }
            } else {
                $property = Property::create([
                    'owner_id' => Auth::id(),
                    'building_name' => $this->buildingName,
                    'address' => $this->address,
                    'prop_description' => $this->description,
                ]);

                $this->storeFiles($property);

                $this->notifySuccess(
                    'Property Created Successfully!',
                    'You can now add units to ' . $this->buildingName
                );

                $this->dispatch('propertyCreated', $property->property_id);
            }

            DB::commit();
            $this->dispatch('refresh-property-list');
            $this->close();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->notifyError(
                'Failed to Save Property',
                'An error occurred while saving the property. Please try again.'
            );
        }
    }

    private function storeFiles(Property $property): void
    {
        // Store property photos
        if (!empty($this->propertyPhotos)) {
            foreach (array_slice($this->propertyPhotos, 0, 5) as $photo) {
                $path = $photo->store('property_photos', 'public');
                PropertyDocument::create([
                    'property_id' => $property->property_id,
                    'file_path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'category' => 'property_photo',
                    'visibility' => 'all',
                ]);
            }
        }

        // Store documents with their categories and visibility
        $documentFields = [
            'businessPermit' => ['category' => 'business_permit', 'visibility' => 'owner_manager'],
            'bir2303' => ['category' => 'bir_2303', 'visibility' => 'owner_manager'],
            'inspectionReport' => ['category' => 'inspection_report', 'visibility' => 'owner_manager'],
            'barangayClearance' => ['category' => 'barangay_clearance', 'visibility' => 'owner_manager'],
            'occupancyPermit' => ['category' => 'occupancy_permit', 'visibility' => 'all'],
        ];

        foreach ($documentFields as $field => $meta) {
            if ($this->{$field}) {
                // If editing, remove old document of the same category
                if ($this->editingPropertyId) {
                    $oldDoc = PropertyDocument::where('property_id', $property->property_id)
                        ->where('category', $meta['category'])
                        ->first();
                    if ($oldDoc) {
                        Storage::disk('public')->delete($oldDoc->file_path);
                        $oldDoc->delete();
                    }
                }

                $path = $this->{$field}->store('property_documents', 'public');
                PropertyDocument::create([
                    'property_id' => $property->property_id,
                    'file_path' => $path,
                    'original_name' => $this->{$field}->getClientOriginalName(),
                    'category' => $meta['category'],
                    'visibility' => $meta['visibility'],
                ]);
            }
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'buildingName',
            'address',
            'description',
            'editingPropertyId',
            'propertyPhotos',
            'newPhotos',
            'businessPermit',
            'bir2303',
            'inspectionReport',
            'barangayClearance',
            'occupancyPermit',
            'existingPhotos',
            'existingDocuments',
            'removedDocumentIds',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.layouts.properties.add-property-modal');
    }
}
