<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Models\Transaction;
use App\Notifications\NewAccount;
use App\Services\FirebaseStorageService;
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

    // --- Mode: 'add', 'transfer', or 'edit' ---
    public string $mode = 'add';

    // --- Stepper ---
    public int $currentStep = 1;
    public int $totalSteps = 4;

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

    // === STEP 1: Profile Information ===
    #[Validate('nullable|image|max:10240')]
    public $profilePicture = null;
    public ?string $existingProfileImg = null;

    #[Validate('required|min:2')]
    public $firstName = '';

    #[Validate('required|min:2')]
    public $lastName = '';

    #[Validate('required')]
    public $gender = '';

    // === STEP 2: Contact & Personal Details ===
    #[Validate('required|numeric|digits:10')]
    public $phoneNumber = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|min:5')]
    public $permanentAddress = '';

    #[Validate('required')]
    public $governmentIdType = '';

    public $governmentIdTypeOther = '';

    #[Validate('required|min:3')]
    public $governmentIdNumber = '';

    #[Validate('nullable|image|max:10240')]
    public $governmentIdImage = null;
    public ?string $existingGovernmentIdImage = null;

    #[Validate('required|min:2')]
    public $companySchool = '';

    #[Validate('required|min:2')]
    public $positionCourse = '';

    #[Validate('required|min:2')]
    public $emergencyContactName = '';

    #[Validate('required')]
    public $emergencyContactRelationship = '';

    public $emergencyContactRelationshipOther = '';

    #[Validate('required|numeric|digits:10')]
    public $emergencyContactNumber = '';

    // === STEP 3: Rent Details ===
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

    // === STEP 4: Payment Details ===
    #[Validate('required|numeric')]
    public $monthlyRate = '';

    #[Validate('required|numeric')]
    public $securityDeposit = '';

    #[Validate('required')]
    public $paymentStatus = '';

    #[Validate('required')]
    public $monthlyDueDate = '';

    // Auto-computed, not user inputs
    public $shortTermPremium = 0;  // PHP 500/month if term < 6 months

    // --- Dropdown Data ---
    public $buildings = [];
    public $units = [];
    public $beds = [];

    public function mount($modalId = null)
    {
        $this->modalId = $modalId ?? uniqid('add_tenant_modal_');
        $this->loadBuildings();
    }

    // --- Stepper Navigation ---
    public function nextStep(): void
    {
        $this->validate($this->stepValidationRules($this->currentStep));

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        // Only allow going to steps that have been validated (previous steps)
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    // --- Open in ADD mode ---
    #[On('open-add-tenant-modal')]
    public function open()
    {
        $this->resetForm();
        $this->mode = 'add';
        $this->currentStep = 1;
        $this->loadBuildings();
        $this->isOpen = true;
    }

    // --- Open in TRANSFER mode ---
    #[On('open-transfer-tenant-modal')]
    public function openTransfer(int $tenantId)
    {
        $this->resetForm();
        $this->mode = 'transfer';
        $this->currentStep = 1;
        $this->totalSteps = 2; // Transfer only has Rent Details + Move In

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->with([
                'leases' => fn($q) => $q->where('status', 'Active')->latest()->limit(1)->with(['bed.unit.property']),
            ])
            ->first();

        if (!$tenant) return;

        $lease = $tenant->leases->first();

        // Block transfer if latest billing is not Paid
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
        $this->currentStep = 1;

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

        // Pre-fill new fields
        $this->permanentAddress              = $tenant->permanent_address ?? '';
        $this->existingGovernmentIdImage     = $tenant->government_id_image ?? null;

        // Handle "Other" ID type
        $knownIdTypes = ['Passport', "Driver's License", 'UMID', 'National ID', 'Postal ID'];
        $storedIdType = $tenant->government_id_type ?? '';
        if ($storedIdType && !in_array($storedIdType, $knownIdTypes)) {
            $this->governmentIdType      = 'Other';
            $this->governmentIdTypeOther = $storedIdType;
        } else {
            $this->governmentIdType      = $storedIdType;
        }

        $this->governmentIdNumber            = $tenant->government_id_number ?? '';
        $this->companySchool                 = $tenant->company_school ?? '';
        $this->positionCourse                = $tenant->position_course ?? '';
        $this->emergencyContactName          = $tenant->emergency_contact_name ?? '';
        $this->emergencyContactNumber        = $tenant->emergency_contact_number ?? '';

        // Handle "Other" relationship
        $knownRelationships = ['Parent', 'Sibling', 'Spouse', 'Friend', 'Guardian'];
        $storedRelationship = $tenant->emergency_contact_relationship ?? '';
        if ($storedRelationship && !in_array($storedRelationship, $knownRelationships)) {
            $this->emergencyContactRelationship      = 'Other';
            $this->emergencyContactRelationshipOther  = $storedRelationship;
        } else {
            $this->emergencyContactRelationship = $storedRelationship;
        }

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
            $this->term                = $lease->term ?? '';
            $this->startDate           = $lease->start_date?->format('Y-m-d') ?? '';
            $this->shift               = $lease->shift ?? '';
            $this->autoRenew           = $lease->auto_renew ?? false;
            $this->monthlyRate         = $lease->contract_rate ?? '';
            $this->securityDeposit     = $lease->security_deposit ?? '';
            $this->paymentStatus       = 'Paid';
            $this->monthlyDueDate      = $lease->monthly_due_date ?? '';
            $this->shortTermPremium    = $lease->short_term_premium ?? 0;
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
                ->when($this->currentBedId, fn($q) => $q->where('bed_id', '!=', $this->currentBedId))
                ->get(['bed_id', 'bed_number']);

            $unit = Unit::find($unitId);
            if ($unit) {
                $this->dormType    = $unit->occupants;
                $this->monthlyRate = $unit->price;
            }
        }
    }

    // Auto-compute short-term premium when term changes
    public function updatedTerm($value)
    {
        if ($value && (int) $value < 6) {
            $this->shortTermPremium = 500;
        } else {
            $this->shortTermPremium = 0;
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

    private function resolvedIdType(): string
    {
        return $this->governmentIdType === 'Other'
            ? $this->governmentIdTypeOther
            : $this->governmentIdType;
    }

    private function resolvedRelationship(): string
    {
        return $this->emergencyContactRelationship === 'Other'
            ? $this->emergencyContactRelationshipOther
            : $this->emergencyContactRelationship;
    }

    private function saveNewTenant(): void
    {
        DB::transaction(function () {
            $firebase = app(FirebaseStorageService::class);

            $photoPath = $this->profilePicture
                ? $firebase->upload($this->profilePicture, 'Images')
                : null;

            $idImagePath = $this->governmentIdImage
                ? $firebase->upload($this->governmentIdImage, 'Images')
                : null;

            $password = PasswordGenerator::generate();

            $user = User::create([
                'first_name'                     => $this->firstName,
                'last_name'                      => $this->lastName,
                'gender'                         => $this->gender,
                'email'                          => $this->email,
                'contact'                        => $this->phoneNumber,
                'role'                           => 'tenant',
                'password'                       => Hash::make($password),
                'profile_img'                    => $photoPath,
                'permanent_address'              => $this->permanentAddress,
                'government_id_type'             => $this->resolvedIdType(),
                'government_id_number'           => $this->governmentIdNumber,
                'government_id_image'            => $idImagePath,
                'company_school'                 => $this->companySchool,
                'position_course'                => $this->positionCourse,
                'emergency_contact_name'         => $this->emergencyContactName,
                'emergency_contact_relationship' => $this->resolvedRelationship(),
                'emergency_contact_number'       => $this->emergencyContactNumber,
            ]);

            Notification::send($user, new NewAccount($user->email, $password, $user->role));

            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            $lease = Lease::create([
                'tenant_id'            => $user->user_id,
                'bed_id'               => $this->selectedBed,
                'status'               => 'Active',
                'term'                 => $this->term,
                'auto_renew'           => $this->autoRenew,
                'start_date'           => $this->startDate,
                'end_date'             => $endDate,
                'contract_rate'        => $this->monthlyRate,
                'advance_amount'       => $this->monthlyRate,
                'security_deposit'     => $this->securityDeposit,
                'move_in'              => $this->startDate,
                'shift'                => $this->shift,
                'monthly_due_date'     => $this->monthlyDueDate,
                'late_payment_penalty' => 100,
                'short_term_premium'   => $this->shortTermPremium,
                'reservation_fee_paid' => 0,
                'early_termination_fee' => 0,
            ]);

            $billing = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => $this->paymentStatus,
            ]);

            // Deposit
            $depTransaction = Transaction::create([
                'billing_id'       => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Deposit',
                'transaction_date' => today(),
                'amount'           => $this->securityDeposit ?? 0,
            ]);
            $depTransaction->update([
                'reference_number' => 'DEP' . now()->format('Ymd') . '-' . str_pad($depTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            // Advance
            $advTransaction = Transaction::create([
                'billing_id'       => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Advance',
                'transaction_date' => today(),
                'amount'           => $this->monthlyRate ?? 0,
            ]);

            $advTransaction->update([
                'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
        });

        $this->notifySuccess(
            'Tenant Added Successfully!',
            $this->firstName . ' ' . $this->lastName . ' has been added to ' . $this->selectedBed . '.'
        );

        session()->flash('success', 'Tenant added successfully!');
    }

    private function saveTransfer(): void
    {
        DB::transaction(function () {
            $oldLease = Lease::find($this->currentLeaseId);
            $oldSecurityDeposit = $oldLease?->security_deposit ?? 0;
            $newSecurityDeposit = $this->securityDeposit;

            if ($this->currentLeaseId) {
                Lease::where('lease_id', $this->currentLeaseId)->update([
                    'status'   => 'Expired',
                    'end_date' => Carbon::today(),
                ]);
            }

            if ($this->currentBedId) {
                Bed::where('bed_id', $this->currentBedId)->update(['status' => 'Vacant']);
            }

            $carryOverDeposit  = $oldSecurityDeposit >= $newSecurityDeposit
                ? $oldSecurityDeposit
                : $newSecurityDeposit;

            $depositShortfall  = $oldSecurityDeposit < $newSecurityDeposit
                ? $newSecurityDeposit - $oldSecurityDeposit
                : 0;

            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            $lease = Lease::create([
                'tenant_id'            => $this->transferFromTenantId,
                'bed_id'               => $this->selectedBed,
                'status'               => 'Active',
                'term'                 => $this->term,
                'auto_renew'           => $this->autoRenew,
                'start_date'           => $this->startDate,
                'end_date'             => $endDate,
                'contract_rate'        => $this->monthlyRate,
                'advance_amount'       => $this->monthlyRate,
                'security_deposit'     => $carryOverDeposit,
                'move_in'              => $this->startDate,
                'shift'                => $this->shift,
                'monthly_due_date'     => $this->monthlyDueDate,
                'late_payment_penalty' => 100,
                'short_term_premium'   => $this->shortTermPremium,
                'reservation_fee_paid' => 0,
                'early_termination_fee' => 0,
            ]);

            $billing = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                'to_pay'       => $this->monthlyRate,
                'amount'       => $this->monthlyRate,
                'status'       => $this->paymentStatus,
            ]);

            $advTransaction = Transaction::create([
                'billing_id'       => $billing->billing_id,
                'reference_number' => 'placeholder',
                'transaction_type' => 'Debit',
                'category'         => 'Advance',
                'transaction_date' => today(),
                'amount'           => $this->monthlyRate,
            ]);
            $advTransaction->update([
                'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
            ]);

            if ($depositShortfall > 0) {
                $depTransaction = Transaction::create([
                    'billing_id'       => $billing->billing_id,
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

            $firebase = app(FirebaseStorageService::class);

            if ($this->profilePicture && $tenant->profile_img) {
                $firebase->delete($tenant->profile_img);
            }

            if ($this->governmentIdImage && $tenant->government_id_image) {
                $firebase->delete($tenant->government_id_image);
            }

            $photoPath = $this->profilePicture
                ? $firebase->upload($this->profilePicture, 'Images')
                : $tenant->profile_img;

            $idImagePath = $this->governmentIdImage
                ? $firebase->upload($this->governmentIdImage, 'Images')
                : $tenant->government_id_image;

            $tenant->update([
                'first_name'                     => $this->firstName,
                'last_name'                      => $this->lastName,
                'gender'                         => $this->gender,
                'email'                          => $this->email,
                'contact'                        => $this->phoneNumber,
                'profile_img'                    => $photoPath,
                'permanent_address'              => $this->permanentAddress,
                'government_id_type'             => $this->resolvedIdType(),
                'government_id_number'           => $this->governmentIdNumber,
                'government_id_image'            => $idImagePath,
                'company_school'                 => $this->companySchool,
                'position_course'                => $this->positionCourse,
                'emergency_contact_name'         => $this->emergencyContactName,
                'emergency_contact_relationship' => $this->resolvedRelationship(),
                'emergency_contact_number'       => $this->emergencyContactNumber,
            ]);

            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            if ($this->editLeaseId) {
                $lease = Lease::find($this->editLeaseId);
                if ($lease) {
                    if ($lease->bed_id != $this->selectedBed) {
                        Bed::where('bed_id', $lease->bed_id)->update(['status' => 'Vacant']);
                        Bed::where('bed_id', $this->selectedBed)->update(['status' => 'occupied']);
                    }

                    $lease->update([
                        'bed_id'               => $this->selectedBed,
                        'term'                 => $this->term,
                        'auto_renew'           => $this->autoRenew,
                        'start_date'           => $this->startDate,
                        'end_date'             => $endDate,
                        'contract_rate'        => $this->monthlyRate,
                        'advance_amount'       => $this->monthlyRate,
                        'security_deposit'     => $this->securityDeposit,
                        'move_in'              => $this->startDate,
                        'shift'                => $this->shift,
                        'monthly_due_date'     => $this->monthlyDueDate,
                        'late_payment_penalty' => 100,
                        'short_term_premium'   => $this->shortTermPremium,
                        'reservation_fee_paid' => 0,
                        'early_termination_fee' => 0,
                    ]);
                }
            }
        });

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

    // Per-step validation rules for the stepper
    protected function stepValidationRules(int $step): array
    {
        if ($this->isTransfer()) {
            // Transfer mode: step 1 = rent details, step 2 = move-in details
            return match ($step) {
                1 => [
                    'selectedBuilding' => 'required',
                    'selectedUnit'     => 'required',
                    'selectedBed'      => 'required',
                    'dormType'         => 'required',
                    'term'             => 'required',
                    'startDate'        => 'required|date',
                    'shift'            => 'required',
                ],
                2 => [
                    'monthlyRate'          => 'required|numeric',
                    'securityDeposit'      => 'required|numeric',
                    'paymentStatus'        => 'required',
                    'monthlyDueDate'       => 'required',
                ],
                default => [],
            };
        }

        return match ($step) {
            1 => [
                'firstName' => 'required|min:2',
                'lastName'  => 'required|min:2',
                'gender'    => 'required',
            ],
            2 => array_merge([
                'permanentAddress'              => 'required|min:5',
                'governmentIdType'              => 'required',
                'governmentIdNumber'            => 'required|min:3',
                'companySchool'                 => 'required|min:2',
                'positionCourse'                => 'required|min:2',
                'emergencyContactName'          => 'required|min:2',
                'emergencyContactRelationship'  => 'required',
                'emergencyContactNumber'        => 'required|numeric|digits:10',
            ],
            $this->governmentIdType === 'Other' ? ['governmentIdTypeOther' => 'required|min:2'] : [],
            $this->emergencyContactRelationship === 'Other' ? ['emergencyContactRelationshipOther' => 'required|min:2'] : [],
            $this->isEdit() ? [
                'phoneNumber' => 'required|numeric|digits:10|unique:users,contact,' . $this->editTenantId . ',user_id',
                'email'       => 'required|email|unique:users,email,' . $this->editTenantId . ',user_id',
            ] : [
                'phoneNumber' => 'required|numeric|digits:10|unique:users,contact',
                'email'       => 'required|email|unique:users,email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ]),
            3 => [
                'selectedBuilding' => 'required',
                'selectedUnit'     => 'required',
                'selectedBed'      => 'required',
                'dormType'         => 'required',
                'term'             => 'required',
                'startDate'        => 'required|date',
                'shift'            => 'required',
            ],
            4 => [
                'monthlyRate'          => 'required|numeric',
                'securityDeposit'      => 'required|numeric',
                'paymentStatus'        => 'required',
                'monthlyDueDate'       => 'required',
            ],
            default => [],
        };
    }

    protected function validationRules(): array
    {
        $rules = [
            'selectedBuilding'     => 'required',
            'selectedUnit'         => 'required',
            'selectedBed'          => 'required',
            'dormType'             => 'required',
            'term'                 => 'required',
            'startDate'            => 'required|date',
            'shift'                => 'required',
            'monthlyRate'          => 'required|numeric',
            'securityDeposit'      => 'required|numeric',
            'paymentStatus'        => 'required',
            'monthlyDueDate'       => 'required',
        ];

        if (!$this->isTransfer()) {
            $rules['firstName']                    = 'required|min:2';
            $rules['lastName']                     = 'required|min:2';
            $rules['gender']                       = 'required';
            $rules['permanentAddress']             = 'required|min:5';
            $rules['governmentIdType']             = 'required';
            $rules['governmentIdNumber']           = 'required|min:3';
            $rules['companySchool']                = 'required|min:2';
            $rules['positionCourse']               = 'required|min:2';
            $rules['emergencyContactName']         = 'required|min:2';
            $rules['emergencyContactRelationship'] = 'required';
            $rules['emergencyContactNumber']       = 'required|numeric|digits:10';

            if ($this->governmentIdType === 'Other') {
                $rules['governmentIdTypeOther'] = 'required|min:2';
            }
            if ($this->emergencyContactRelationship === 'Other') {
                $rules['emergencyContactRelationshipOther'] = 'required|min:2';
            }

            if ($this->isEdit()) {
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
            'currentStep',
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
            'permanentAddress',
            'governmentIdType',
            'governmentIdTypeOther',
            'governmentIdNumber',
            'governmentIdImage',
            'existingGovernmentIdImage',
            'companySchool',
            'positionCourse',
            'emergencyContactName',
            'emergencyContactRelationship',
            'emergencyContactRelationshipOther',
            'emergencyContactNumber',
            'selectedBuilding',
            'selectedUnit',
            'selectedBed',
            'dormType',
            'term',
            'startDate',
            'shift',
            'autoRenew',
            'monthlyRate',
            'securityDeposit',
            'paymentStatus',
            'monthlyDueDate',
            'shortTermPremium',
            'units',
            'beds',
        ]);
        $this->totalSteps = 4;
    }

    public function render()
    {
        return view('livewire.layouts.tenants.add-tenant-modal', [
            'isTransfer' => $this->mode === 'transfer',
            'isEdit'     => $this->mode === 'edit',
        ]);
    }
}
