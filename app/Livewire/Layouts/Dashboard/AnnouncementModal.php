<?php

namespace App\Livewire\Layouts\Dashboard;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Announcement;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Support\Facades\Auth; // ← ADD THIS IMPORT
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class AnnouncementModal extends Component
{
    use WithNotifications;

    public $showModal = false;
    // Removed: public $showConfirmation = false; (No longer needed)

    public $headline = '';
    public $details = '';
    public $propertyId = null;
    public $notificationDate = null;
    public $editingAnnouncementId = null;

    public $properties = [];

    protected $rules = [
        'headline' => 'required|min:3|max:200',
        'details' => 'required|min:10|max:1000',
        'propertyId' => 'required|exists:properties,property_id',
        'notificationDate' => 'required|date|after_or_equal:today',
    ];

    protected $messages = [
        'headline.required' => 'Please enter a headline for your announcement.',
        'details.required' => 'Please enter details for your announcement.',
        'propertyId.required' => 'Please select a property.',
        'notificationDate.required' => 'Please select a notification date.',
        'notificationDate.date' => 'The notification date must be a valid date.',
        'notificationDate.after_or_equal' => 'Past dates are not allowed for announcements.',
    ];

    protected $listeners = ['open-announcement-modal' => 'openModal', 'edit-announcement' => 'editAnnouncement'];

    public function mount()
    {
        if (Auth::user()->role == "landlord") // ← CHANGE TO Auth::user()
        {
            $this->properties = Property::where('owner_id', Auth::id()) // ← CHANGE TO Auth::id()
                ->orderBy('building_name')
                ->get();
        } else if (Auth::user()->role == "manager") // ← CHANGE TO Auth::user()
        {
            $propertyIds = Unit::where('manager_id', Auth::id()) // ← CHANGE TO Auth::id()
                ->pluck('property_id')
                ->unique();

            $this->properties = Property::whereIn('property_id', $propertyIds)
                ->orderBy('building_name')
                ->get();
        }
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function editAnnouncement($announcementId)
    {
        $announcement = Announcement::findOrFail($announcementId);
        
        $this->editingAnnouncementId = $announcementId;
        $this->headline = $announcement->headline;
        $this->details = $announcement->details;
        $this->propertyId = $announcement->property_id;
        $this->notificationDate = $announcement->notification_date?->format('Y-m-d');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        // Removed: $this->showConfirmation = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->headline = '';
        $this->details = '';
        $this->propertyId = null;
        $this->notificationDate = null;
        $this->editingAnnouncementId = null;
        $this->resetValidation();
    }

    // This is the final action called by the <x-ui.modal-confirm> component
    public function confirmPost()
    {
        $this->validate();

        try {
            if ($this->editingAnnouncementId) {
                // Update existing announcement
                $announcement = Announcement::findOrFail($this->editingAnnouncementId);
                $announcement->update([
                    'headline' => $this->headline,
                    'details' => $this->details,
                    'property_id' => $this->propertyId,
                    'notification_date' => $this->notificationDate,
                ]);

                $this->notifySuccess('Announcement updated!', 'Your changes are now visible.');
            } else {
                // Create new announcement
                $announcement = new Announcement();

                if (Auth::user()->role === "landlord") // ← CHANGE TO Auth::user()
                {
                    $announcement = $this->saveToDatabase('manager');
                } else if (Auth::user()->role === "manager") { // ← CHANGE TO Auth::user()
                    $announcement = $this->saveToDatabase('tenant');
                }

                $this->sendEmailToRecipient($announcement);
                $this->notifySuccess('Announcement posted!', 'Your announcement has been sent successfully.');
            }

            $this->closeModal();
            $this->dispatch('announcement-posted');
        } catch (\Throwable $e) {
            Log::error('Failed to save announcement.', [
                'user_id' => Auth::id(),
                'is_editing' => (bool) $this->editingAnnouncementId,
                'property_id' => $this->propertyId,
                'error' => $e->getMessage(),
            ]);

            $this->notifyError('Announcement failed', 'An error occurred while saving your announcement.');
        }
    }

    private function saveToDatabase($recipientRole)
    {
        $announcement = Announcement::create([
            'author_id' => Auth::id(), // ← CHANGE TO Auth::id()
            'property_id' => $this->propertyId,
            'headline' => $this->headline,
            'details' => $this->details,
            'sender_role' => Auth::user()->role, // ← CHANGE TO Auth::user()
            'recipient_role' => $recipientRole,
            'notification_date' => $this->notificationDate,
            'created_at' => now()
        ]);

        return $announcement;
    }

    private function sendEmailToRecipient($announcement)
    {
        $property = Property::find($this->propertyId);

        if (Auth::user()->role === "landlord") { // ← CHANGE TO Auth::user()
            $managers = $property->managers;

            Notification::send($managers, new NewAnnouncement($announcement));
        } else if (Auth::user()->role === "manager") { // ← CHANGE TO Auth::user()
            $tenants = $property->tenantsForManager(Auth::id())->get(); // ← CHANGE TO Auth::id()

            Notification::send($tenants, new NewAnnouncement($announcement));
        }
    }

    public function render()
    {
        return view('livewire.layouts.dashboard.announcement-modal');
    }
}