<?php

namespace App\Livewire\Actions;

use App\Livewire\Concerns\WithNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsForm extends Component
{
    use WithNotifications, WithFileUploads;

    // Form properties
    public string $firstName = '';
    public string $lastName = '';
    public string $phoneNumber = '';
    public string $email = '';

    // Image upload properties
    #[Validate('nullable|image|max:10240')]
    public $profilePicture;

    #[Validate('nullable|image|max:10240')]
    public $governmentIdImage;

    // Existing image paths from DB
    public ?string $existingProfileImg = null;
    public ?string $existingGovernmentIdImage = null;

    // Snapshot of loaded values for dirty-checking
    public string $originalFirstName = '';
    public string $originalLastName = '';
    public string $originalPhoneNumber = '';
    public string $originalEmail = '';
    public ?string $originalProfileImg = null;
    public ?string $originalGovernmentIdImage = null;

    public bool $hasPendingChanges = false;

    public function mount(): void
    {
        $this->loadUserData();
    }

    public function updatedPhoneNumber($value): void
    {
        $this->phoneNumber = substr(preg_replace('/\D/', '', (string) $value), 0, 10);
        $this->recomputePendingChanges();
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'firstName',
            'lastName',
            'email',
            'profilePicture',
            'governmentIdImage',
            'existingProfileImg',
            'existingGovernmentIdImage',
        ], true)) {
            $this->recomputePendingChanges();
        }
    }

    private function loadUserData(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            $this->firstName = '';
            $this->lastName = '';
            $this->phoneNumber = '';
            $this->email = '';
            $this->existingProfileImg = null;
            $this->existingGovernmentIdImage = null;
            $this->profilePicture = null;
            $this->governmentIdImage = null;

            $this->syncOriginalState();
            $this->hasPendingChanges = false;

            return;
        }

        $this->email = (string) ($user->email ?? '');
        $this->phoneNumber = substr(preg_replace('/\D/', '', (string) ($user->contact ?? '')), 0, 10);
        $this->firstName = (string) ($user->first_name ?? '');
        $this->lastName = (string) ($user->last_name ?? '');
        $this->existingProfileImg = $user->profile_img;
        $this->existingGovernmentIdImage = $user->government_id_image;
        $this->profilePicture = null;
        $this->governmentIdImage = null;

        $this->syncOriginalState();
        $this->hasPendingChanges = false;
    }

    public function getExistingProfileImgUrlProperty(): ?string
    {
        return $this->resolvePublicFileUrl($this->existingProfileImg);
    }

    public function getExistingGovernmentIdImageUrlProperty(): ?string
    {
        return $this->resolvePublicFileUrl($this->existingGovernmentIdImage);
    }

    private function resolvePublicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = $this->normalizeStoragePath($path);

        if (!Storage::disk('public')->exists($normalized)) {
            return null;
        }

        return Storage::url($normalized);
    }

    private function normalizeStoragePath(string $path): string
    {
        $normalized = ltrim(trim($path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return $normalized;
    }

    public function removeProfilePicture(): void
    {
        $this->profilePicture = null;
        $this->existingProfileImg = null;
        $this->recomputePendingChanges();
    }

    public function removeGovernmentIdImage(): void
    {
        $this->governmentIdImage = null;
        $this->existingGovernmentIdImage = null;
        $this->recomputePendingChanges();
    }

    private function syncOriginalState(): void
    {
        $this->originalFirstName = trim((string) $this->firstName);
        $this->originalLastName = trim((string) $this->lastName);
        $this->originalEmail = trim((string) $this->email);
        $this->originalPhoneNumber = preg_replace('/\D/', '', (string) $this->phoneNumber);
        $this->originalProfileImg = $this->existingProfileImg;
        $this->originalGovernmentIdImage = $this->existingGovernmentIdImage;
    }

    private function recomputePendingChanges(): void
    {
        $formFirstName = trim((string) $this->firstName);
        $formLastName = trim((string) $this->lastName);
        $formEmail = trim((string) $this->email);
        $formPhone = preg_replace('/\D/', '', (string) $this->phoneNumber);

        $this->hasPendingChanges = $formFirstName !== $this->originalFirstName
            || $formLastName !== $this->originalLastName
            || $formEmail !== $this->originalEmail
            || $formPhone !== $this->originalPhoneNumber
            || $this->profilePicture !== null
            || $this->governmentIdImage !== null
            || $this->existingProfileImg !== $this->originalProfileImg
            || $this->existingGovernmentIdImage !== $this->originalGovernmentIdImage;
    }

    public function confirmSave(): void
    {
        $this->recomputePendingChanges();

        if (!$this->hasPendingChanges) {
            $this->notifyInfo('No changes detected', 'Update any field before saving.');
            return;
        }

        $this->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|digits:10',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id() . ',user_id',
        ]);

        $this->dispatch('open-save-confirm-modal');
    }

    public function save(): void
    {
        $this->recomputePendingChanges();

        if (!$this->hasPendingChanges) {
            $this->notifyInfo('No changes detected', 'Update any field before saving.');
            return;
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            $this->notifyError('Session expired', 'Please sign in again.');
            return;
        }

        $this->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|digits:10',
            'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'profilePicture' => 'nullable|image|max:10240',
            'governmentIdImage' => 'nullable|image|max:10240',
        ]);

        $updateData = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'contact' => $this->phoneNumber,
        ];

        // Handle profile picture upload
        if ($this->profilePicture) {
            if ($user->profile_img) {
                Storage::disk('public')->delete($user->profile_img);
            }
            $updateData['profile_img'] = $this->profilePicture->store('profile-photos', 'public');
        } elseif ($this->existingProfileImg === null && $user->profile_img) {
            Storage::disk('public')->delete($user->profile_img);
            $updateData['profile_img'] = null;
        }

        // Handle government ID image upload
        if ($this->governmentIdImage) {
            if ($user->government_id_image) {
                Storage::disk('public')->delete($user->government_id_image);
            }
            $updateData['government_id_image'] = $this->governmentIdImage->store('government-ids', 'public');
        } elseif ($this->existingGovernmentIdImage === null && $user->government_id_image) {
            Storage::disk('public')->delete($user->government_id_image);
            $updateData['government_id_image'] = null;
        }

        $user->update($updateData);
        $user->refresh();

        $this->profilePicture = null;
        $this->governmentIdImage = null;
        $this->existingProfileImg = $user->profile_img;
        $this->existingGovernmentIdImage = $user->government_id_image;
        $this->syncOriginalState();
        $this->hasPendingChanges = false;

        $this->dispatch('profile-updated');
        $this->notifySuccess('Settings Saved Successfully!', 'Your personal information has been updated.');
    }

    public function cancelSave(): void
    {
        $this->loadUserData();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.forms.owner-details');
    }
}
