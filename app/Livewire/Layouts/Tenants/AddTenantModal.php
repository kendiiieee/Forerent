<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Notifications\NewAccount;
use App\Services\PasswordGenerator;
use App\Livewire\Concerns\WithNotifications;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Bed;
use App\Models\Lease;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AddTenantModal extends Component
{
    use WithFileUploads, WithNotifications;

    public $isOpen = false;
    public $modalId;

    // --- Mode: 'add' or 'transfer' ---
    public string $mode = 'add';

    // Transfer-specific: holds the tenant being transferred
    public ?int $transferFromTenantId = null;
    public ?int $currentLeaseId       = null;
    public ?int $currentBedId         = null;

    // --- Profile Information ---
    #[Validate('nullable|image|max:10240')]
    public $profilePicture = null;
    public ?string $existingProfileImg = null; // used in transfer mode (already stored path)

    #[Validate('required|min:2')]
    public $firstName = '';

    #[Validate('required|min:2')]
    public $lastName = '';

    #[Validate('required')]
    public $gender = '';

    // --- Contact Information ---
    #[Validate('required|numeric|digits:10')]
    public $phoneNumber = '';

    #[Validate('required|email')]
    public $email = '';

    // --- Rent Details ---
    #[Validate('required')]
    public $selectedBuilding = '';

    #[Validate('required')]
    public $selectedUnit = '';

    #[Validate('required')]
    public $selectedBed = '';

    #[Validate('required')]
    public $dormType = '';

    #[Validate('required')]
    public $term = '';

    #[Validate('required|date')]
    public $startDate = '';

    #[Validate('required')]
    public $shift = '';

    public $autoRenew = false;

    // --- Move In Details ---
    #[Validate('required|date')]
    public $moveInDate = '';

    #[Validate('required|numeric')]
    public $monthlyRate = '';

    #[Validate('required|numeric')]
    public $securityDeposit = '';

    #[Validate('required')]
    public $paymentStatus = '';

    public $registration = '';

    // --- Dropdown Data ---
    public $buildings = [];
    public $units = [];
    public $beds = [];

    public function mount($modalId = null)
    {
        $this->modalId = $modalId ?? uniqid('add_tenant_modal_');
        $this->loadBuildings();
    }

    // --- Open in ADD mode ---
    #[On('open-add-tenant-modal')]
    public function open()
    {
        $this->resetForm();
        $this->mode = 'add';
        $this->loadBuildings();
        $this->isOpen = true;
    }

    // --- Open in TRANSFER mode ---
    #[On('open-transfer-tenant-modal')]
    public function openTransfer(int $tenantId)
    {
        $this->resetForm();
        $this->mode = 'transfer';

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->with([
                'leases' => fn($q) => $q->where('status', 'Active')->latest()->limit(1)->with('bed'),
            ])
            ->first();

        if (!$tenant) {
            return;
        }

        $lease = $tenant->leases->first();

        // Pre-fill read-only fields
        $this->transferFromTenantId = $tenant->user_id;
        $this->firstName            = $tenant->first_name;
        $this->lastName             = $tenant->last_name;
        $this->gender               = $tenant->gender ?? '';
        $this->phoneNumber          = $tenant->contact ?? '';
        $this->email                = $tenant->email;
        $this->existingProfileImg   = $tenant->profile_img;

        // Store current lease/bed to vacate on save
        $this->currentLeaseId = $lease?->lease_id;
        $this->currentBedId   = $lease?->bed_id;

        $this->loadBuildings();
        $this->isOpen = true;
    }

    public function close()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->isOpen = false;
    }

    protected function loadBuildings()
    {
        $this->buildings = Property::whereHas('units', function ($query) {
            $query->where('manager_id', Auth::id());
        })->get(['property_id', 'building_name']);
    }

    public function updatedSelectedBuilding($propertyId)
    {
        $this->selectedUnit = '';
        $this->selectedBed  = '';
        $this->units        = [];
        $this->beds         = [];

        if ($propertyId) {
            $this->units = Unit::where('property_id', $propertyId)
                ->where('manager_id', Auth::id())
                ->get(['unit_id', 'unit_number']);
        }
    }

    public function updatedSelectedUnit($unitId)
    {
        $this->selectedBed = '';
        $this->beds        = [];
        $this->dormType    = '';
        $this->monthlyRate = '';

        if ($unitId) {
            $this->beds = Bed::where('unit_id', $unitId)
                ->where('status', 'Vacant')
                // In transfer mode, exclude the tenant's current bed
                ->when($this->currentBedId, fn($q) => $q->where('bed_id', '!=', $this->currentBedId))
                ->get(['bed_id', 'bed_number']);

            $unit = Unit::find($unitId);
            if ($unit) {
                $this->dormType    = $unit->occupants;
                $this->monthlyRate = $unit->price;
            }
        }
    }

    public function validateAndConfirm(): void
    {
        $this->validate($this->validationRules());
        $confirmModal = $this->isTransfer() ? 'transfer-tenant-confirmation' : 'save-tenant-confirmation';
        $this->dispatch('open-modal', $confirmModal);
    }

    public function save()
    {
        $this->validate($this->validationRules());

        $this->isTransfer() ? $this->saveTransfer() : $this->saveNewTenant();

        $this->isOpen = false;
        $this->dispatch('refresh-tenant-list');
        $this->resetForm();
    }

    private function saveNewTenant(): void
    {
        DB::transaction(function () {
            $photoPath = $this->profilePicture
                ? $this->profilePicture->store('profile-photos', 'public')
                : null;

            $password = PasswordGenerator::generate();

            $user = User::create([
                'first_name'  => $this->firstName,
                'last_name'   => $this->lastName,
                'email'       => $this->email,
                'contact'     => $this->phoneNumber,
                'role'        => 'tenant',
                'password'    => Hash::make($password),
                'profile_img' => $photoPath,
            ]);

            Notification::send($user, new NewAccount($user->email, $password, $user->role));

            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            $lease = Lease::create([
                'tenant_id'        => $user->user_id,
                'bed_id'           => $this->selectedBed,
                'status'           => 'Active',
                'term'             => $this->term,
                'auto_renew'       => $this->autoRenew,
                'start_date'       => $this->startDate,
                'end_date'         => $endDate,
                'contract_rate'    => $this->monthlyRate,
                'advance_amount'   => $this->monthlyRate,
                'security_deposit' => $this->securityDeposit,
                'move_in'          => $this->moveInDate,
                'shift'            => $this->shift,
            ]);

            $billingDate = Carbon::parse($this->startDate)->addMonth();

            Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => $billingDate,
                'next_billing' => $billingDate->copy()->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => 'Unpaid',
            ]);

            Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
        });

        // Show success toast notification
        $this->notifySuccess(
            'Tenant Added Successfully!',
            $this->firstName . ' ' . $this->lastName . ' has been added to ' . $this->selectedBed . '.'
        );

        session()->flash('success', 'Tenant added successfully!');
    }

    private function saveTransfer(): void
    {
        DB::transaction(function () {
            // 1. Close the current lease and vacate the old bed
            if ($this->currentLeaseId) {
                Lease::where('lease_id', $this->currentLeaseId)->update([
                    'status'   => 'Expired',
                    'end_date' => Carbon::today(),
                ]);
            }

            if ($this->currentBedId) {
                Bed::where('bed_id', $this->currentBedId)->update(['status' => 'Vacant']);
            }

            // 2. Create a new lease on the new bed
            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            $lease = Lease::create([
                'tenant_id'        => $this->transferFromTenantId,
                'bed_id'           => $this->selectedBed,
                'status'           => 'Active',
                'term'             => $this->term,
                'auto_renew'       => $this->autoRenew,
                'start_date'       => $this->startDate,
                'end_date'         => $endDate,
                'contract_rate'    => $this->monthlyRate,
                'advance_amount'   => $this->monthlyRate,
                'security_deposit' => $this->securityDeposit,
                'move_in'          => $this->moveInDate,
                'shift'            => $this->shift,
            ]);

            $billingDate = Carbon::parse($this->startDate)->addMonth();

            Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => $billingDate,
                'next_billing' => $billingDate->copy()->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => 'Unpaid',
            ]);

            // 3. Mark new bed as occupied
            Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
        });

        // Reload the detail panel for the transferred tenant
        $this->dispatch('tenantSelected', tenantId: $this->transferFromTenantId);
        session()->flash('success', 'Tenant transferred successfully!');
    }

    // --- Helpers ---
    public function isTransfer(): bool
    {
        return $this->mode === 'transfer';
    }

    protected function validationRules(): array
    {
        $rules = [
            'selectedBuilding' => 'required',
            'selectedUnit'     => 'required',
            'selectedBed'      => 'required',
            'dormType'         => 'required',
            'term'             => 'required',
            'startDate'        => 'required|date',
            'shift'            => 'required',
            'moveInDate'       => 'required|date',
            'monthlyRate'      => 'required|numeric',
            'securityDeposit'  => 'required|numeric',
            'paymentStatus'    => 'required',
        ];

        // Only validate personal info fields when adding a new tenant
        if (!$this->isTransfer()) {
            $rules['firstName']   = 'required|min:2';
            $rules['lastName']    = 'required|min:2';
            $rules['gender']      = 'required';
            $rules['phoneNumber'] = 'required|numeric|digits:10|unique:users,contact';
            $rules['email']       = 'required|email|unique:users,email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        }

        return $rules;
    }

    private function resetForm()
    {
        $this->reset([
            'mode',
            'transferFromTenantId',
            'currentLeaseId',
            'currentBedId',
            'profilePicture',
            'existingProfileImg',
            'firstName',
            'lastName',
            'gender',
            'phoneNumber',
            'email',
            'selectedBuilding',
            'selectedUnit',
            'selectedBed',
            'dormType',
            'term',
            'startDate',
            'shift',
            'autoRenew',
            'moveInDate',
            'monthlyRate',
            'securityDeposit',
            'paymentStatus',
            'registration',
            'units',
            'beds',
        ]);
    }

    public function render()
    {
        return view('livewire.layouts.tenants.add-tenant-modal', [
            'isTransfer' => $this->mode === 'transfer',
        ]);
    }
}
