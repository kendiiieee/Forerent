<?php

namespace App\Livewire\Actions;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SettingsForm extends Component
{
    // Form properties
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $email;

    // Modal control property (optional, Flowbite JS might handle it)
    // public $showConfirmationModal = false;

    public function mount()
    {
        // 1. Call the new method to load initial data
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
        // Optional: Run validation here before modal shows?
        // If validation passes, Flowbite JS will show the modal.
        // If it fails, Livewire updates the view with errors, modal won't show.
        $this->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|digits:10',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id() . ',user_id',
        ]);

        // If validation passes, Flowbite's data-modal-toggle will show the modal.
        // We don't strictly need PHP to control the modal visibility here,
        // unless you want more complex logic.
        // $this->showConfirmationModal = true;
    }


    /**
     * This method is triggered by the modal's "Yes, save changes" button.
     */
    public function save()
    {
        /** @var \App\Models\User $user */ // <-- ADD THIS LINE
        $user = Auth::user();

        // Re-run validation just in case (optional but safe)
        $validatedData = $this->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|digits:10',
            'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
        ]);

        // Update the user
        $user->update([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'contact' => $this->phoneNumber,
        ]);

        // Close the modal (optional, Flowbite JS does this via data-modal-hide)
        // $this->showConfirmationModal = false;

        // Send a success message
        session()->flash('success', 'Settings saved successfully.');

        // Optional: Dispatch browser event if you need JS to react after save
        // $this->dispatch('settings-saved');
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
