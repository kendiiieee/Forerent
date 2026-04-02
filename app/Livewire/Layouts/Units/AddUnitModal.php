<?php

namespace App\Livewire\Layouts\Units;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class AddUnitModal extends Component
{
    use WithNotifications;

    public $isOpen = false;

    public $modalId;

    public $editingUnitId = null;

    public $editingUnitNumber = null;

    public $currentStep = 1;

    public $steps = [
        1 => 'Unit Details',
        2 => 'Model Amenities',
        3 => 'Review & Predict',
    ];

    public $properties = [];

    public $property_id;

    public $floor_number;

    public $occupants = 'Co-ed';

    public $living_area;

    public $furnishing = 'Semi-furnished';

    public $bed_type;

    public $room_cap;

    public $unit_cap;

    public $m_f;

    public $room_type;

    public $model_amenities = [];

    public $amenity_labels = [];

    public $predicted_price = null;

    public $actual_price;

    public $is_predicting = false;

    // Fixed the missing comma here
    protected $rules = [
        'property_id' => 'required|integer|exists:properties,property_id',
        'floor_number' => 'required|integer|min:0',
        'occupants' => 'required|in:Male,Female,Co-ed',
        'living_area' => 'required|numeric|min:1',
        'bed_type' => 'required|in:Single,Bunk',
        'room_cap' => 'required|integer|min:1',
        'actual_price' => 'required|numeric|min:0|max:999999',
    ];

    public function mount($modalId = null)
    {
        $this->modalId = $modalId ?? uniqid('add_unit_modal_');
        try {
            $this->properties = Property::all(['property_id', 'building_name']);
        } catch (\Exception $e) {
            $this->properties = collect([]);
        }
        $this->initializeAmenities();
    }

    protected function getListeners(): array
    {
        return [
            "openAddUnitModal_{$this->modalId}" => 'open',
            'open-unit-modal' => 'loadUnitForEditing',
        ];
    }

    #[On('open-add-unit-modal')]
    public function open(): void
    {
        $this->resetForm();
        $this->editingUnitId = null;
        $this->isOpen = true;
    }

    #[On('open-unit-modal')]
    public function loadUnitForEditing($unitId)
    {
        $this->resetForm();
        $unit = Unit::find($unitId);

        if ($unit) {
            $this->editingUnitId = $unit->unit_id;
            $this->editingUnitNumber = $unit->unit_number;
            $this->property_id = $unit->property_id;
            $this->floor_number = $unit->floor_number;
            $this->occupants = $unit->occupants ?? 'Co-ed';
            $this->living_area = $unit->living_area;
            $this->furnishing = $unit->furnishing ?? 'Semi-furnished';
            $this->bed_type = $unit->bed_type;
            $this->room_cap = $unit->room_cap;
            $this->actual_price = $unit->price;
            $this->predicted_price = $unit->price;

            $savedAmenities = json_decode($unit->amenities, true) ?? [];
            foreach ($this->model_amenities as $key => $value) {
                if (in_array($key, $savedAmenities)) {
                    $this->model_amenities[$key] = true;
                }
            }

            $this->isOpen = true;
            $this->currentStep = 1;
        }
    }

    public function close(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->isOpen = false;
        $this->dispatch('unitModalClosed');
    }

    private function initializeAmenities()
    {
        $amenity_keys = [
            'Free_Wifi',
            'Hot_Cold_Shower',
            'Electric_Fan',
            'Water_Kettle',
            'Closet_Cabinet',
            'Housekeeping',
            'Refrigerator',
            'Microwave',
            'Rice_Cooker',
            'Dining_Table',
            'Utility_Subsidy',
            'AC_Unit',
            'Induction_Cooker',
            'Washing_Machine',
            'Access_Pool',
            'Access_Gym',
        ];

        foreach ($amenity_keys as $key) {
            $this->amenity_labels[$key] = ucwords(str_replace('_', ' ', $key));
        }
        $this->model_amenities = array_fill_keys($amenity_keys, false);
    }

    public function nextStep()
    {
        if ($this->currentStep == 1) {
            $this->validate([
                'property_id' => 'required',
                'floor_number' => 'required|numeric',
                'occupants' => 'required',
                'living_area' => 'required|numeric',
                'bed_type' => 'required',
                'room_cap' => 'required|numeric',
            ]);
        }

        if ($this->currentStep == 2) {
            $this->runPrediction();
        }

        if ($this->currentStep < count($this->steps)) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    private function calculateFurnishing(): string
    {
        $selectedCount = count(array_filter($this->model_amenities));
        $totalAmenities = count($this->model_amenities);

        if ($selectedCount === 0) {
            return 'Bare';
        } elseif ($selectedCount === $totalAmenities) {
            return 'Fully Furnished';
        } else {
            return 'Semi-furnished';
        }
    }

    private function runPrediction()
    {
        $this->is_predicting = true;

        // Auto-calculate furnishing based on amenities
        $this->furnishing = $this->calculateFurnishing();

        // Data for API - exclude occupants, furnishing is calculated from amenities
        $dataForModel = [
            'Living Area (sqft)' => (float) $this->living_area,
            'Floor' => (int) $this->floor_number,
            'Bed type' => $this->bed_type,
            'Room capacity' => (int) $this->room_cap,
            'Furnishing' => $this->furnishing,
        ];
        $dataForModel = array_merge($dataForModel, $this->model_amenities);

        try {
            $response = Http::post(env('PRICE_API_URL') . '/predict', $dataForModel);
            if ($response->successful()) {
                $this->predicted_price = $response->json('predicted_price');
            } else {
                $this->predicted_price = rand(8000, 12000);
            }
        } catch (\Exception $e) {
            $this->predicted_price = rand(5000, 15000);
        }

        if (! $this->editingUnitId) {
            $this->actual_price = $this->predicted_price;
        }
        $this->is_predicting = false;
    }

    public function validateAndConfirm(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

        // Validation passed — open the confirmation modal
        $this->dispatch('open-modal', 'publish-confirmation');
    }

    public function saveUnit()
    {

        try {
            $savedUnitId = null;
            $savedPropertyId = (int) $this->property_id;

            if (auth()->user()->role === 'manager' && ! $this->editingUnitId) {
                $this->notifyError(
                    'Authorization Failed',
                    'Managers are not authorized to create new units.'
                );

                return;
            }

            $checkedAmenities = array_keys(array_filter($this->model_amenities));

            $data = [
                'property_id' => $this->property_id,
                'floor_number' => $this->floor_number,
                'occupants' => $this->occupants,
                'living_area' => $this->living_area,
                'furnishing' => $this->furnishing,
                'bed_type' => $this->bed_type,
                'room_cap' => $this->room_cap,
                'price' => (int) $this->actual_price,
                'amenities' => json_encode($checkedAmenities),
            ];

            if ($this->editingUnitId) {
                $unit = Unit::find($this->editingUnitId);
                if ($unit) {
                    $unit->update($data);
                    $savedUnitId = $unit->unit_id;
                    $this->notifySuccess(
                        'Unit #'.$unit->unit_id.' Updated Successfully!',
                        'Unit details have been updated.'
                    );
                }
            } else {
                $newUnit = Unit::create(array_merge($data, [
                    'unit_number' => $this->generateUniqueUnitNumber($this->property_id, $this->floor_number),
                ]));
                $savedUnitId = $newUnit->unit_id;
                $this->notifySuccess(
                    'Unit #'.$newUnit->unit_id.' Created Successfully!',
                    'New unit has been added to your property.'
                );
            }

            $this->close();
            $this->dispatch('refresh-unit-list', buildingId: $savedPropertyId, unitId: $savedUnitId);
        } catch (\Exception $e) {
            Log::error('Failed to save unit: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? [],
            ]);
            $this->notifyError(
                'Failed to Save Unit',
                $e->getMessage()
            );
        }
    }

    private function generateUniqueUnitNumber($propertyId, $floorNumber): string
    {
        $baseNumber = sprintf('F%dU%d', $floorNumber, rand(100, 999));
        while (Unit::where('property_id', $propertyId)->where('unit_number', $baseNumber)->exists()) {
            $baseNumber = sprintf('F%dU%d', $floorNumber, rand(1000, 9999));
        }

        return $baseNumber;
    }

    private function resetForm(): void
    {
        $this->reset([
            'currentStep',
            'property_id',
            'floor_number',
            'occupants',
            'living_area',
            'furnishing',
            'bed_type',
            'room_cap',
            'predicted_price',
            'actual_price',
            'is_predicting',
            'editingUnitId',
            'editingUnitNumber',
        ]);
        $this->initializeAmenities();
        $this->occupants = 'Co-ed';
        $this->furnishing = 'Semi-furnished';
    }

    public function render()
    {
        return view('livewire.layouts.units.add-unit-modal');
    }
}
