<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AddUserForm extends Form
{
    public $firstName = '';
    public $lastName = '';
    public $phoneNumber = '';
    public $email = '';

    public ?int $userId = null;

    public function rules(): array
    {
        // Remove non-numeric characters from phone number for validation
        $cleanedPhone = preg_replace('/[^0-9]/', '', $this->phoneNumber);

        return [
            'firstName' => 'required|string|min:2|max:50',
            'lastName' => 'required|string|min:2|max:50',
            'phoneNumber' => [
                'required',
                function ($attribute, $value, $fail) use ($cleanedPhone) {
                    if (strlen($cleanedPhone) !== 10) {
                        $fail('Phone number must be exactly 10 digits.');
                    }
                },
                Rule::unique('users', 'contact')->ignore($this->userId, 'user_id')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->userId, 'user_id')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'First name is required.',
            'phoneNumber.required' => 'Phone number is required.',
            'phoneNumber.regex' => 'Phone number must be exactly 10 digits.',
            'phoneNumber.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already registered.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'firstName' => 'first name',
            'lastName' => 'last name',
            'phoneNumber' => 'phone number',
            'email' => 'email address',
        ];
    }

    public function setUser(User $user)
    {
        $this->userId = $user->user_id;
        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->phoneNumber = $user->contact;
        $this->email = $user->email;
    }

    public function store($role = 'manager', ?string $plainPassword = null)
    {
        $this->validate();

        // Clean phone number - keep only digits
        $cleanedPhone = preg_replace('/[^0-9]/', '', $this->phoneNumber);

        return User::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'contact' => $cleanedPhone,
            'email' => $this->email,
            'role' => $role,
            'password' => Hash::make($plainPassword ?? 'password'),
            'email_verified_at' => now(),
        ]);
    }

    public function update(User $user)
    {
        $this->validate();

        // Clean phone number - keep only digits
        $cleanedPhone = preg_replace('/[^0-9]/', '', $this->phoneNumber);

        $user->update([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'contact' => $cleanedPhone,
            'email' => $this->email,
        ]);

        return $user;
    }
}
