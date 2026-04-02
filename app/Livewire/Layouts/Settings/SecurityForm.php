<?php

namespace App\Livewire\Layouts\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithNotifications;

class SecurityForm extends Component
{
    use WithNotifications;

    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    public array $passwordRequirementRules = [];

    public function mount(): void
    {
        $this->passwordRequirementRules = $this->buildPasswordRequirementRules();
    }

    private function buildPasswordRequirementRules(): array
    {
        return [
            [
                'key' => 'length',
                'label' => 'At least 8 characters',
                'type' => 'min',
                'value' => 8,
            ],
            [
                'key' => 'number',
                'label' => 'At least one number',
                'type' => 'regex',
                'pattern' => '[0-9]',
            ],
            [
                'key' => 'special',
                'label' => 'At least one special character',
                'type' => 'regex',
                'pattern' => '[\\W_]',
            ],
            [
                'key' => 'capital',
                'label' => 'At least one uppercase letter',
                'type' => 'regex',
                'pattern' => '[A-Z]',
            ],
        ];
    }

    private function passwordValidationRules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()->mixedCase()->symbols(),
            ],
        ];
    }

    public function clearFields()
    {
        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();
    }

    public function requestPasswordChangeConfirmation(): void
    {
        $this->validate($this->passwordValidationRules());

        $this->dispatch('open-modal', 'security-password-confirmation');
    }

    public function updatePassword()
    {
        $this->validate($this->passwordValidationRules());

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->forceFill([
                'password' => Hash::make($this->password),
            ])->save();

            // Reset inputs
            $this->reset(['current_password', 'password', 'password_confirmation']);

            $this->notifySuccess('Password Updated Successfully!', 'You will be redirected to login shortly.');

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirectRoute('login', navigate: true);
        } catch (\Exception $e) {
            Log::error('Password Update Failed: ' . $e->getMessage());
            $this->addError('current_password', 'Something went wrong. Please try again later.');
            $this->notifyError('Password update failed', 'Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.layouts.settings.security-form');
    }
}
