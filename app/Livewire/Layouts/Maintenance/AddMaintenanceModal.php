<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;

class AddMaintenanceModal extends Component
{
    use WithFileUploads;

    public $modalId;
    public $isOpen = false;

    public $category    = 'Plumbing';
    public $description = '';       // Owned by Alpine via setDescription(), NOT wire:model
    public $urgency     = 'Level 2'; // Auto-assigned, not shown to tenant
    public $images      = [];        // Up to 3 images

    public $residentName  = '';
    public $unitNumber    = '';
    public $buildingName  = '';
    public $ticketNumber  = '';
    public $leaseId       = null;

    // Called by Alpine @input handler — completely bypasses Livewire DOM morphing
    public function setDescription(string $value): void
    {
        $this->description = $value;
    }

    #[On('open-maintenance-modal')]
    public function open()
    {
        $this->resetForm();
        $this->loadTenantDetails();
        $this->isOpen = true;
    }

    public function mount()
    {
        $this->modalId = 'maintenance-modal-' . uniqid();
    }

    #[On('close-modal')]
    public function closeModalHandler($modalName)
    {
        if ($modalName === 'discard-maintenance-confirmation') {
            $this->close();
        }
    }

    public function loadTenantDetails()
    {
        $user = Auth::user();
        $this->residentName = $user->first_name . ' ' . $user->last_name;

        $nextId = (DB::table('maintenance_requests')->max('request_id') ?? 0) + 1;
        $this->ticketNumber = 'MR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $leaseDetails = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->where('leases.tenant_id', $user->user_id)
            ->where('leases.status', 'Active')
            ->select('leases.lease_id', 'units.unit_number', 'properties.building_name')
            ->first();

        if ($leaseDetails) {
            $this->leaseId      = $leaseDetails->lease_id;
            $this->unitNumber   = 'Unit ' . $leaseDetails->unit_number;
            $this->buildingName = $leaseDetails->building_name;
        } else {
            $this->leaseId      = null;
            $this->unitNumber   = 'No Active Unit';
            $this->buildingName = 'N/A';
        }
    }

    public function selectCategory($name)
    {
        $this->category = $name;
    }

    public function validateAndConfirm()
    {
        try {
            $this->validate([
                'category'    => 'required|in:Plumbing,Electrical,Structural,Appliance,Pest Control',
                'description' => 'required|string|min:10|max:2000',
                'images'      => 'nullable|array|max:3',
                'images.*'    => 'image|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

        if (!$this->leaseId) {
            $this->addError('description', 'You must have an active lease to submit a request.');
            $this->dispatch('scroll-to-error');
            return;
        }

        $this->dispatch('open-modal', 'save-maintenance-confirmation');
    }

    public function save()
    {
        // Store up to 3 images as JSON array in image_path column
        $imagePaths = [];
        if (!empty($this->images)) {
            foreach (array_slice($this->images, 0, 3) as $img) {
                $imagePaths[] = $img->store('maintenance_photos', 'public');
            }
        }

        $user = Auth::user();

        DB::table('maintenance_requests')->insert([
            'lease_id'      => $this->leaseId,
            'ticket_number' => $this->ticketNumber,
            'problem'       => $this->description,
            'category'      => $this->category,
            'status'        => 'Pending',
            'urgency'       => $this->urgency,
            'logged_by'     => $user->first_name . ' ' . $user->last_name,
            'log_date'      => Carbon::now()->format('Y-m-d'),
            'image_path'    => !empty($imagePaths) ? json_encode($imagePaths) : null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->dispatch('close-modal', 'save-maintenance-confirmation');
        $this->dispatch('refresh-maintenance-list');
        $this->close();
    }

    public function removeImage($index)
    {
        array_splice($this->images, $index, 1);
    }

    public function close()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['description', 'images', 'leaseId']);
        $this->category = 'Plumbing';
        $this->urgency  = 'Level 2';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.add-maintenance-modal');
    }
}
