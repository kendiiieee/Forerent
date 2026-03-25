<?php

namespace App\Livewire\Actions;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Livewire\Concerns\WithNotifications;

class SettingsForm extends Component
{
    use WithNotifications, WithFileUploads;

    // Form properties
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $email;

    // Image upload properties
    #[Validate('nullable|image|max:10240')]
    public $profilePicture;

    #[Validate('nullable|image|max:10240')]
    public $governmentIdImage;

    // Existing image paths from DB
    public $existingProfileImg;
    public $existingGovernmentIdImage;

    public function mount()
    {
        $this->loadUserData();
    }

    public function updatedPhoneNumber($value)
    {
        $this->phoneNumber = substr(preg_replace('/\D/', '', (string) $value), 0, 10);
    }

    private function loadUserData()
    {
        $user = Auth::user();

        $this->email = $user->email;
        $this->phoneNumber = $user->contact;
        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->existingProfileImg = $user->profile_img;
        $this->existingGovernmentIdImage = $user->government_id_image;
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

    public function removeProfilePicture()
    {
        $this->profilePicture = null;
        $this->existingProfileImg = null;
    }

    public function removeGovernmentIdImage()
    {
        $this->governmentIdImage = null;
        $this->existingGovernmentIdImage = null;
    }

    /**
     * This method is triggered by the form's wire:submit.
     * Its primary job now might be just validation before showing the modal,
     * or it can be removed if the button directly triggers the modal via JS.
     * Let's keep it simple: the button's data attributes handle showing the modal.
     * The modal's "Yes" button will call the actual save method.
     */
    public function confirmSave()
    {
        $this->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|digits:10',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id() . ',user_id',
        ]);

        // Validation passed — open the Flowbite confirmation modal via JS
        $this->dispatch('open-save-confirm-modal');
    }


    /**
     * This method is triggered by the modal's "Yes, save changes" button.
     */
    public function save()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

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

        // Reset file inputs and reload from DB
        $this->profilePicture = null;
        $this->governmentIdImage = null;
        $this->existingProfileImg = $user->profile_img;
        $this->existingGovernmentIdImage = $user->government_id_image;

        $this->dispatch('profile-updated');

        $this->notifySuccess('Settings Saved Successfully!', 'Your personal information has been updated.');
    }

    /**
     * This method is triggered by the modal's "No, cancel" button.
     */
    public function cancelSave()
    {
        // Just closes the modal (Flowbite JS handles this via data-modal-hide)
        // $this->showConfirmationModal = false;
        $this->loadUserData();
    }


    public function render()
    {
        return view('livewire.forms.owner-details');
    }
}
