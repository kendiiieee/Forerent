<?php

namespace App\Livewire\Layouts\Managers;

use App\Livewire\Concerns\WithNotifications;
use App\Livewire\Forms\AddUserForm;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\NewAccount;
use App\Services\PasswordGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class AddManagerModal extends Component
{
    use WithFileUploads, WithNotifications;

    public $isOpen = false;
    public $modalId;

    #[Validate('nullable|image|max:2048')]
    public $profilePicture = null;

    public AddUserForm $userForm;

    #[Validate('nullable')]
    public $selectedBuilding = '';

    #[Validate('nullable')]
    public $selectedFloor = '';

    #[Validate('nullable')]
    public $selectedUnits = [];

    // Accumulated selections: ['propertyId_floor' => [unitId, unitId, ...]]
    public $allSelectedUnits = [];

    public $buildings = [];
    public $floors = [];
    public $availableUnits = [];
    public ?int $managerId = null;
    public bool $isEditing = false;

    public function mount($modalId = null)
    {
        $this->modalId = $modalId ?? uniqid('add_manager_modal_');
        $this->loadBuildings();
    }

    protected function getListeners(): array
    {
        return [
            "openManagerModal_{$this->modalId}" => 'open',
        ];
    }

    public function open($managerId = null): void
    {
        $this->resetForm();

        if ($managerId) {
            $manager = User::find($managerId);

            if ($manager) {
                $this->isEditing = true;
                $this->managerId = $manager->user_id;
                $this->userForm->setUser($manager);

                // Pre-populate allSelectedUnits with ALL existing assignments
                $existingUnits = Unit::where('manager_id', $manager->user_id)
                    ->whereHas('property', fn($q) => $q->where('owner_id', Auth::id()))
                    ->get(['unit_id', 'property_id', 'floor_number']);

                foreach ($existingUnits as $unit) {
                    $key = $unit->property_id . '_' . $unit->floor_number;
                    $this->allSelectedUnits[$key][] = (string) $unit->unit_id;
                }

                $firstUnit = $existingUnits->first();

                if ($firstUnit) {
                    $this->selectedBuilding = $firstUnit->property_id;
                    $this->updatedSelectedBuilding($firstUnit->property_id);

                    $this->selectedFloor = $firstUnit->floor_number;
                    $this->updatedSelectedFloor($firstUnit->floor_number);
                }
            }
        }

        $this->isOpen = true;
    }

    public function loadBuildings(): void
    {
        $this->buildings = Property::where('owner_id', Auth::id())
            ->get(['property_id', 'building_name']);
    }

    public function updatedSelectedUnits(): void
    {
        if ($this->selectedBuilding && $this->selectedFloor) {
            $key = $this->selectedBuilding . '_' . $this->selectedFloor;
            $this->allSelectedUnits[$key] = $this->selectedUnits;
        }
    }

    public function updatedSelectedBuilding($propertyId): void
    {
        $this->selectedFloor = '';
        $this->selectedUnits = [];
        $this->floors = [];
        $this->availableUnits = [];

        if ($propertyId) {
            $this->floors = Unit::where('property_id', $propertyId)
                ->distinct()
                ->orderBy('floor_number')
                ->pluck('floor_number')
                ->toArray();
        }
    }

    public function updatedSelectedFloor($floor): void
    {
        $this->availableUnits = [];
        $this->selectedUnits = [];

        if ($this->selectedBuilding && $floor) {
            $this->availableUnits = $this->getUnitsForFloor($this->selectedBuilding, $floor, $this->managerId);

            // Restore previously saved selections for this building+floor
            $key = $this->selectedBuilding . '_' . $floor;
            $this->selectedUnits = $this->allSelectedUnits[$key] ?? [];
        }
    }

    private function getUnitsForFloor($propertyId, $floor, $managerId = null): array
    {
        $key = $propertyId . '_' . $floor;
        $pendingUnitIds = array_map('intval', $this->allSelectedUnits[$key] ?? []);

        $units = Unit::where('property_id', $propertyId)
            ->where('floor_number', $floor)
            ->whereHas('property', function ($q) {
                $q->where('owner_id', Auth::id());
            })
            ->where(function ($query) use ($managerId, $pendingUnitIds) {
                $query->whereNull('manager_id');
                if (!is_null($managerId)) {
                    $query->orWhere('manager_id', $managerId);
                }
                if (!empty($pendingUnitIds)) {
                    $query->orWhereIn('unit_id', $pendingUnitIds);
                }
            })
            ->orderBy('unit_id')
            ->get(['unit_id', 'manager_id', 'unit_number']);

        return $units->map(fn($unit) => [
            'id' => $unit->unit_id,
            'number' => "Unit {$unit->unit_number}",
            'checked' => $unit->manager_id == $managerId,
        ])->toArray();
    }

    public function validateAndConfirm(): void
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

        $this->dispatch('open-modal', 'save-manager-confirmation');
    }

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->isEditing) {
                $originalManager = User::find($this->managerId);
                $originalFloor = Unit::where('manager_id', $this->managerId)->value('floor_number');
                $manager = $this->userForm->update($originalManager);

                $changedFields = [];
                if ($originalManager->first_name !== $manager->first_name) $changedFields[] = 'first name';
                if ($originalManager->last_name !== $manager->last_name)   $changedFields[] = 'last name';
                if ($originalManager->contact !== $manager->contact)       $changedFields[] = 'phone number';
                if ($originalManager->email !== $manager->email)           $changedFields[] = 'email';
                if ($this->selectedFloor && $originalFloor != $this->selectedFloor) $changedFields[] = 'floor assignment';
                if ($this->profilePicture && !is_string($this->profilePicture))     $changedFields[] = 'profile picture';

                $changeMessage = !empty($changedFields)
                    ? ucfirst(implode(', ', $changedFields)) . ' updated for ' . $manager->first_name . '.'
                    : $manager->first_name . ' has been updated.';
            } else {
                $tempPassword = PasswordGenerator::generate();
                $manager = $this->userForm->store('manager', $tempPassword);

                // Email delivery failure should not block manager creation.
                try {
                    Notification::send($manager, new NewAccount($manager->email, $tempPassword, $manager->role));
                } catch (\Throwable $notificationError) {
                    Log::warning('Manager account created but notification email failed.', [
                        'manager_id' => $manager->user_id,
                        'email' => $manager->email,
                        'error' => $notificationError->getMessage(),
                    ]);

                    $this->notifyWarning(
                        'Manager saved, email not sent',
                        'The manager was created successfully, but the account email could not be delivered.'
                    );
                }

                $changeMessage = $manager->first_name . ' added successfully as a manager!';
            }

            // Handle profile picture upload
            if ($this->profilePicture && !is_string($this->profilePicture)) {
                if ($this->isEditing && $manager->profile_img) {
                    $this->deleteStoredImage($manager->profile_img);
                }

                $path = $this->profilePicture->store('profile-photos', 'public');
                $manager->update(['profile_img' => $path]);
            }

            // Flatten all selected unit IDs across every building+floor
            $allSelectedUnitIds = array_map(
                'intval',
                array_merge(...array_values($this->allSelectedUnits) ?: [[]])
            );

            if (!empty($allSelectedUnitIds)) {
                Unit::where('manager_id', $manager->user_id)
                    ->whereNotIn('unit_id', $allSelectedUnitIds)
                    ->update(['manager_id' => null]);

                Unit::whereIn('unit_id', $allSelectedUnitIds)
                    ->update(['manager_id' => $manager->user_id]);
            } else {
                Unit::where('manager_id', $manager->user_id)
                    ->update(['manager_id' => null]);
            }

            $this->notifySuccess(
                $this->isEditing ? 'Manager Updated!' : 'Manager Added!',
                $changeMessage
            );

            $this->close();
            $this->dispatch('refresh-manager-list');
            $this->dispatch('managerUpdated', managerId: $manager->user_id);
            $this->dispatch('close-modal', 'save-manager-confirmation');

        } catch (\Throwable $e) {
            Log::error('Failed to save manager.', [
                'manager_id' => $this->managerId,
                'is_editing' => $this->isEditing,
                'selected_building' => $this->selectedBuilding,
                'selected_floor' => $this->selectedFloor,
                'selected_units_count' => count($this->selectedUnits ?? []),
                'error' => $e->getMessage(),
            ]);

            $this->notifyError(
                'Failed to Save Manager',
                'An error occurred while saving the manager. Please try again.'
            );
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['profilePicture', 'selectedBuilding', 'selectedFloor', 'selectedUnits', 'allSelectedUnits', 'floors', 'availableUnits', 'managerId', 'isEditing']);
        $this->userForm->reset();
        $this->resetValidation();
    }

    private function deleteStoredImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            $normalized = ltrim(trim((string) parse_url($path, PHP_URL_PATH) ?: $path), '/');

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
                Storage::disk('public')->delete($normalized);
            }
        } catch (\Throwable $exception) {
            // File may not exist on Render ephemeral filesystem after redeploy
            Log::debug('Could not delete stored image (may be expected on Render redeploy).', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.layouts.managers.add-manager-modal');
    }
}
