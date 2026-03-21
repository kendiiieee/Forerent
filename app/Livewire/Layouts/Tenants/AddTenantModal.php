<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Models\Transaction;
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

    // Transfer/Edit-specific
    public ?int $transferFromTenantId = null;
    public ?int $editTenantId         = null;
    public ?int $editLeaseId          = null;
    public ?int $currentLeaseId       = null;
    public ?int $currentBedId         = null;

    // Current lease details (read-only display in transfer mode)
    public $currentBuilding  = '';
    public $currentUnit      = '';
    public $currentBed       = '';
    public $currentDormType  = '';
    public $currentTerm      = '';
    public $currentShift     = '';
    public $currentStartDate = '';
    public $currentEndDate   = '';
    public $currentRate      = '';
    public $currentAutoRenew = false;

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
                'leases' => fn($q) => $q->where('status', 'Active')->latest()->limit(1)->with(['bed.unit.property']),
            ])
            ->first();

        if (!$tenant) return;

        $lease = $tenant->leases->first();

        // ── Block transfer if latest billing is not Paid ──────────────────────
        $latestBilling = Billing::where('lease_id', $lease?->lease_id)
            ->latest('billing_date')
            ->first();

        if (!$latestBilling || $latestBilling->status !== 'Paid') {
            $this->notifyError(
                'Transfer Not Allowed',
                'This tenant has an outstanding balance. Please settle the latest billing before transferring.'
            );
            return;
        }

        // Pre-fill read-only fields
        $this->transferFromTenantId = $tenant->user_id;
        $this->firstName            = $tenant->first_name;
        $this->lastName             = $tenant->last_name;
        $this->gender               = $tenant->gender ?? '';
        $this->phoneNumber          = preg_replace('/\D/', '', $tenant->contact ?? '');
        $this->email                = $tenant->email;
        $this->existingProfileImg   = $tenant->profile_img;

        // Store current lease/bed to vacate on save
        $this->currentLeaseId = $lease?->lease_id;
        $this->currentBedId   = $lease?->bed_id;

        // Populate current lease details for display
        if ($lease) {
            $bed  = $lease->bed;
            $unit = $bed?->unit;

            $this->currentBuilding  = $unit?->property?->building_name ?? '—';
            $this->currentUnit      = $unit?->unit_number ?? '—';
            $this->currentBed       = $bed?->bed_number ?? '—';
            $this->currentDormType  = $unit?->occupants ?? '—';
            $this->currentTerm      = $lease->term ? $lease->term . ' Months' : '—';
            $this->currentShift     = $lease->shift ?? '—';
            $this->currentStartDate = $lease->start_date ? Carbon::parse($lease->start_date)->format('M d, Y') : '—';
            $this->currentEndDate   = $lease->end_date ? Carbon::parse($lease->end_date)->format('M d, Y') : '—';
            $this->currentRate      = $lease->contract_rate ? '₱ ' . number_format($lease->contract_rate, 0) : '—';
            $this->currentAutoRenew = $lease->auto_renew ?? false;
        }

        $this->loadBuildings();
        $this->isOpen = true;
    }

    // --- Open in EDIT mode ---
    #[On('open-edit-tenant-modal')]
    public function openEdit(int $tenantId)
    {
        $this->resetForm();
        $this->mode = 'edit';

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->with([
                'leases' => fn($q) => $q->where('status', 'Active')->latest()->limit(1)->with(['bed.unit.property']),
            ])
            ->first();

        if (!$tenant) {
            return;
        }

        $lease = $tenant->leases->first();
        $bed   = $lease?->bed;
        $unit  = $bed?->unit;

        // Store IDs for update
        $this->editTenantId = $tenant->user_id;
        $this->editLeaseId  = $lease?->lease_id;

        // Pre-fill personal info
        $this->firstName        = $tenant->first_name;
        $this->lastName         = $tenant->last_name;
        $this->gender           = $tenant->gender ?? '';
        $this->phoneNumber      = preg_replace('/\D/', '', $tenant->contact ?? '');
        $this->email            = $tenant->email;
        $this->existingProfileImg = $tenant->profile_img;

        // Pre-fill rent details
        if ($unit) {
            $this->selectedBuilding = $unit->property_id;
            $this->units = Unit::where('property_id', $unit->property_id)
                ->where('manager_id', Auth::id())
                ->get(['unit_id', 'unit_number']);

            $this->selectedUnit = $unit->unit_id;
            $this->dormType     = $unit->occupants ?? '';

            $this->beds = Bed::where('unit_id', $unit->unit_id)
                ->where(function ($q) use ($bed) {
                    $q->where('status', 'Vacant')
                      ->orWhere('bed_id', $bed?->bed_id);
                })
                ->get(['bed_id', 'bed_number']);

            $this->selectedBed = $bed?->bed_id;
        }

        // Pre-fill lease details
        if ($lease) {
            $this->term            = $lease->term ?? '';
            $this->startDate       = $lease->start_date?->format('Y-m-d') ?? '';
            $this->shift           = $lease->shift ?? '';
            $this->autoRenew       = $lease->auto_renew ?? false;
            $this->moveInDate      = $lease->move_in?->format('Y-m-d') ?? '';
            $this->monthlyRate     = $lease->contract_rate ?? '';
            $this->securityDeposit = $lease->security_deposit ?? '';
            $this->paymentStatus   = 'Paid';
        }

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
        $confirmModal = match ($this->mode) {
            'transfer' => 'transfer-tenant-confirmation',
            'edit'     => 'edit-tenant-confirmation',
            default    => 'save-tenant-confirmation',
        };
        $this->dispatch('open-modal', $confirmModal);
    }

    public function save()
    {
        $this->validate($this->validationRules());

        match ($this->mode) {
            'transfer' => $this->saveTransfer(),
            'edit'     => $this->saveEditTenant(),
            default    => $this->saveNewTenant(),
        };

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

            $billing = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => 'Unpaid',
            ]);

            // Deposit
            $depTransaction = Transaction::create([
                'billing_id' => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category' => 'Deposit',
                'transaction_date' => today(),
                'amount' => $this->lease->security_deposit ?? 0,
            ]);
            $depTransaction->update([
                'reference_number' => 'DEP' . now()->format('Ymd') . '-' . str_pad($depTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            // Advance
            $advTransaction = Transaction::create([
                'billing_id' => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category' => 'Advance',
                'transaction_date' => today(),
                'amount' => $this->lease->advance_amount ?? 0,
            ]);

            $advTransaction->update([
                'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
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
            // 1. Get old lease to retrieve its security deposit
            $oldLease = Lease::find($this->currentLeaseId);
            $oldSecurityDeposit = $oldLease?->security_deposit ?? 0;
            $newSecurityDeposit = $this->securityDeposit;

            // 2. Close current lease and vacate old bed
            if ($this->currentLeaseId) {
                Lease::where('lease_id', $this->currentLeaseId)->update([
                    'status'   => 'Expired',
                    'end_date' => Carbon::today(),
                ]);
            }

            if ($this->currentBedId) {
                Bed::where('bed_id', $this->currentBedId)->update(['status' => 'Vacant']);
            }

            // 3. Resolve security deposit carry-over
            // If old >= new → keep old deposit as-is (tenant pays nothing extra)
            // If old <  new → tenant pays the difference
            $carryOverDeposit  = $oldSecurityDeposit >= $newSecurityDeposit
                ? $oldSecurityDeposit   // carry the full old amount as new deposit
                : $newSecurityDeposit;  // new deposit is higher, use new value

            $depositShortfall  = $oldSecurityDeposit < $newSecurityDeposit
                ? $newSecurityDeposit - $oldSecurityDeposit
                : 0;

            // 4. Create new lease with resolved deposit
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
                'security_deposit' => $carryOverDeposit,
                'move_in'          => $this->moveInDate,
                'shift'            => $this->shift,
            ]);


            $billing = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => 'Unpaid',
            ]);

            // 6. Advance payment transaction (always required on transfer)
            $advTransaction = Transaction::create([
                'billing_id' => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Advance',
                'transaction_date' => today(),
                'amount'           => $this->monthlyRate,
            ]);
            $advTransaction->update([
                'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            // 7. Deposit transaction — only charge shortfall if old < new
            if ($depositShortfall > 0) {
                $depTransaction = Transaction::create([
                    'billing_id' => $billing->billing_id,
                    'reference_number' => 'placeholder',
                    'transaction_type' => 'Debit',
                    'category'         => 'Deposit',
                    'transaction_date' => today(),
                    'amount'           => $depositShortfall,
                ]);
                $depTransaction->update([
                    'reference_number' => 'DEP' . now()->format('Ymd') . '-' . str_pad($depTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
                ]);
            }

            // 8. Mark new bed as occupied
            Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
        });

        $this->dispatch('tenantSelected', tenantId: $this->transferFromTenantId);

        $this->notifySuccess(
            'Tenant Transferred Successfully!',
            $this->firstName . ' ' . $this->lastName . ' has been transferred to the new bed.'
        );

        session()->flash('success', 'Tenant transferred successfully!');
    }

    private function saveEditTenant(): void
    {
        DB::transaction(function () {
            $tenant = User::find($this->editTenantId);
            if (!$tenant) return;

            // Update profile photo if a new one was uploaded
            $photoPath = $this->profilePicture
                ? $this->profilePicture->store('profile-photos', 'public')
                : $tenant->profile_img;

            // Update tenant personal info
            $tenant->update([
                'first_name'  => $this->firstName,
                'last_name'   => $this->lastName,
                'email'       => $this->email,
                'contact'     => $this->phoneNumber,
                'profile_img' => $photoPath,
            ]);

            // Update or create lease
            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            if ($this->editLeaseId) {
                $lease = Lease::find($this->editLeaseId);
                if ($lease) {
                    // If bed changed, vacate old bed and occupy new one
                    if ($lease->bed_id != $this->selectedBed) {
                        Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Vacant']);
                        Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
                    }

                    $lease->update([
                        'bed_id'           => $this->selectedBed,
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
                }
            }
        });

        // Reload the detail panel
        $this->dispatch('tenantSelected', tenantId: $this->editTenantId);

        $this->notifySuccess(
            'Tenant Updated Successfully!',
            $this->firstName . ' ' . $this->lastName . '\'s details have been updated.'
        );
    }

    // --- Helpers ---
    public function isTransfer(): bool
    {
        return $this->mode === 'transfer';
    }

    public function isEdit(): bool
    {
        return $this->mode === 'edit';
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

        // Validate personal info for add and edit modes (not transfer)
        if (!$this->isTransfer()) {
            $rules['firstName']   = 'required|min:2';
            $rules['lastName']    = 'required|min:2';
            $rules['gender']      = 'required';

            if ($this->isEdit()) {
                // Exclude current tenant from unique checks
                $rules['phoneNumber'] = 'required|numeric|digits:10|unique:users,contact,' . $this->editTenantId . ',user_id';
                $rules['email']       = 'required|email|unique:users,email,' . $this->editTenantId . ',user_id';
            } else {
                $rules['phoneNumber'] = 'required|numeric|digits:10|unique:users,contact';
                $rules['email']       = 'required|email|unique:users,email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
            }
        }

        return $rules;
    }

    private function resetForm()
    {
        $this->reset([
            'mode',
            'transferFromTenantId',
            'editTenantId',
            'editLeaseId',
            'currentLeaseId',
            'currentBedId',
            'currentBuilding',
            'currentUnit',
            'currentBed',
            'currentDormType',
            'currentTerm',
            'currentShift',
            'currentStartDate',
            'currentEndDate',
            'currentRate',
            'currentAutoRenew',
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
            'isEdit'     => $this->mode === 'edit',
        ]);
    }
}
