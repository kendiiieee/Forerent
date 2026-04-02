<?php

namespace App\Livewire\Actions;

use App\Livewire\Concerns\WithNotifications;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

    public function hydrate(): void
    {
        if ($this->hasPendingChanges || !Auth::check()) {
            return;
        }

        $user = $this->resolveCurrentUser();

        if (!$user) {
            return;
        }

        $shouldReload = ($this->firstName === '' && (string) ($user->first_name ?? '') !== '')
            || ($this->lastName === '' && (string) ($user->last_name ?? '') !== '')
            || ($this->email === '' && (string) ($user->email ?? '') !== '')
            || ($this->phoneNumber === '' && $this->normalizePhone((string) ($user->contact ?? '')) !== '');

        if ($shouldReload) {
            $this->loadUserData($user);
        }
    }

    public function updatedPhoneNumber($value): void
    {
        $this->phoneNumber = $this->normalizePhone((string) $value);
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

    private function loadUserData(?User $user = null): void
    {
        $user ??= $this->resolveCurrentUser();

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

        $this->email = (string) ($user->getAttribute('email') ?? '');
        $this->phoneNumber = $this->normalizePhone((string) ($user->getAttribute('contact') ?? ''));
        $this->firstName = (string) ($user->getAttribute('first_name') ?? '');
        $this->lastName = (string) ($user->getAttribute('last_name') ?? '');
        $this->existingProfileImg = $user->profile_img;
        $this->existingGovernmentIdImage = $user->government_id_image;
        $this->profilePicture = null;
        $this->governmentIdImage = null;

        $this->syncOriginalState();
        $this->hasPendingChanges = false;
    }

    private function resolveCurrentUser(): ?User
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();

        if (!$authUser) {
            return null;
        }

        /** @var \App\Models\User|null $freshUser */
        $freshUser = User::query()->find($authUser->getKey());

        return $freshUser ?? $authUser;
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        // Keep the local mobile part shown next to +63 in the UI.
        if (strlen($digits) > 10) {
            return substr($digits, -10);
        }

        return $digits;
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

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
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

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            $normalized = ltrim((string) parse_url($normalized, PHP_URL_PATH), '/');
        }

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
        $this->originalPhoneNumber = $this->normalizePhone((string) $this->phoneNumber);
        $this->originalProfileImg = $this->existingProfileImg;
        $this->originalGovernmentIdImage = $this->existingGovernmentIdImage;
    }

    private function recomputePendingChanges(): void
    {
        $formFirstName = trim((string) $this->firstName);
        $formLastName = trim((string) $this->lastName);
        $formEmail = trim((string) $this->email);
        $formPhone = $this->normalizePhone((string) $this->phoneNumber);

        $this->hasPendingChanges = $formFirstName !== $this->originalFirstName
            || $formLastName !== $this->originalLastName
            || $formEmail !== $this->originalEmail
            || $formPhone !== $this->originalPhoneNumber
            || $this->profilePicture !== null
            || $this->governmentIdImage !== null
            || $this->existingProfileImg !== $this->originalProfileImg
            || $this->existingGovernmentIdImage !== $this->originalGovernmentIdImage;
    }

    public function save(): void
    {
        $this->firstName = trim((string) $this->firstName);
        $this->lastName = trim((string) $this->lastName);
        $this->email = trim((string) $this->email);
        $this->phoneNumber = $this->normalizePhone((string) $this->phoneNumber);

        $this->recomputePendingChanges();

        if (!$this->hasPendingChanges) {
            $this->notifyInfo('No changes detected', 'Update any field before saving.');
            return;
        }

        $user = $this->resolveCurrentUser();

        if (!$user) {
            $this->notifyError('Session expired', 'Please sign in again.');
            return;
        }

        try {
            $this->validate([
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'phoneNumber' => ['required', 'digits:10', Rule::unique('users', 'contact')->ignore($user->user_id, 'user_id')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
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
                // Note: Only delete old image when uploading a new one
                if ($user->profile_img && $this->existingProfileImg !== $user->profile_img) {
                    $this->deleteStoredImage($user->profile_img);
                }
                $updateData['profile_img'] = $this->profilePicture->store('profile-photos', 'public');
            } elseif ($this->existingProfileImg === null && $user->profile_img) {
                // Only delete if user explicitly removed the image
                $this->deleteStoredImage($user->profile_img);
                $updateData['profile_img'] = null;
            }

            // Handle government ID image upload
            if ($this->governmentIdImage) {
                // Note: Only delete old image when uploading a new one
                if ($user->government_id_image && $this->existingGovernmentIdImage !== $user->government_id_image) {
                    $this->deleteStoredImage($user->government_id_image);
                }
                $updateData['government_id_image'] = $this->governmentIdImage->store('government-ids', 'public');
            } elseif ($this->existingGovernmentIdImage === null && $user->government_id_image) {
                // Only delete if user explicitly removed the image
                $this->deleteStoredImage($user->government_id_image);
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
        } catch (\Throwable $exception) {
            Log::error('Settings save failed.', [
                'user_id' => $user->user_id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function cancelSave(): void
    {
        $this->loadUserData();
        $this->resetValidation();
    }

    private function deleteStoredImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            $normalized = $this->normalizeStoragePath($path);

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
        return view('livewire.forms.owner-details');
    }
}
