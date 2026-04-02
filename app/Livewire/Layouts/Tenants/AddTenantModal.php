<?php

namespace App\Livewire\Layouts\Tenants;

use App\Broadcasting\SendGridChannel;
use App\Mail\NewAccountSmtpMail;
use App\Models\Billing;
use App\Models\Transaction;
use App\Notifications\NewAccount;
use App\Services\PasswordGenerator;
use App\Livewire\Concerns\WithNotifications;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        try {
            $this->validate($this->stepValidationRules($this->currentStep));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

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
        $this->totalSteps = 2;

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->with([
                'leases' => fn($q) => $q->where('status', 'Active')->latest()->limit(1)->with(['bed.unit.property']),
            ])
            ->first();

        if (!$tenant) return;

        $lease = $tenant->leases->first();

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

        $this->transferFromTenantId = $tenant->user_id;
        $this->firstName            = $tenant->first_name;
        $this->lastName             = $tenant->last_name;
        $this->gender               = $tenant->gender ?? '';
        $this->phoneNumber          = preg_replace('/\D/', '', $tenant->contact ?? '');
        $this->email                = $tenant->email;
        $this->existingProfileImg   = $tenant->profile_img;

        $this->currentLeaseId = $lease?->lease_id;
        $this->currentBedId   = $lease?->bed_id;

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
            $this->currentRate      = $lease->contract_rate ? '₱ ' . number_format((float) $lease->contract_rate, 0) : '—';
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

        $this->editTenantId = $tenant->user_id;
        $this->editLeaseId  = $lease?->lease_id;

        $this->firstName          = $tenant->first_name;
        $this->lastName           = $tenant->last_name;
        $this->gender             = $tenant->gender ?? '';
        $this->phoneNumber        = preg_replace('/\D/', '', $tenant->contact ?? '');
        $this->email              = $tenant->email;
        $this->existingProfileImg = $tenant->profile_img;

        $this->permanentAddress          = $tenant->permanent_address ?? '';
        $this->existingGovernmentIdImage = $tenant->government_id_image ?? null;

        $knownIdTypes = ['Passport', "Driver's License", 'UMID', 'National ID', 'Postal ID'];
        $storedIdType = $tenant->government_id_type ?? '';
        if ($storedIdType && !in_array($storedIdType, $knownIdTypes)) {
            $this->governmentIdType      = 'Other';
            $this->governmentIdTypeOther = $storedIdType;
        } else {
            $this->governmentIdType = $storedIdType;
        }

        $this->governmentIdNumber     = $tenant->government_id_number ?? '';
        $this->companySchool          = $tenant->company_school ?? '';
        $this->positionCourse         = $tenant->position_course ?? '';
        $this->emergencyContactName   = $tenant->emergency_contact_name ?? '';
        $this->emergencyContactNumber = $tenant->emergency_contact_number ?? '';

        $knownRelationships = ['Parent', 'Sibling', 'Spouse', 'Friend', 'Guardian'];
        $storedRelationship = $tenant->emergency_contact_relationship ?? '';
        if ($storedRelationship && !in_array($storedRelationship, $knownRelationships)) {
            $this->emergencyContactRelationship      = 'Other';
            $this->emergencyContactRelationshipOther = $storedRelationship;
        } else {
            $this->emergencyContactRelationship = $storedRelationship;
        }

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

        if ($lease) {
            $this->term             = $lease->term ?? '';
            $this->startDate        = $lease->start_date ? Carbon::parse($lease->start_date)->format('Y-m-d') : '';
            $this->shift            = $lease->shift ?? '';
            $this->autoRenew        = $lease->auto_renew ?? false;
            $this->monthlyRate      = $lease->contract_rate ?? '';
            $this->securityDeposit  = $lease->security_deposit ?? '';
            $this->paymentStatus    = 'Paid';
            $this->monthlyDueDate   = $lease->monthly_due_date ?? '';
            $this->shortTermPremium = $lease->short_term_premium ?? 0;
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
        $ownerIds = Property::whereHas('units', function ($query) {
            $query->where('manager_id', Auth::id());

            // In add/transfer mode, only show buildings that still have at least one available bed.
            if (!$this->isEdit()) {
                $query->whereHas('beds', function ($bedQuery) {
                    $bedQuery->where('status', 'Vacant');
                });
            }
        })->pluck('owner_id')->unique();

        $this->buildings = Property::whereIn('owner_id', $ownerIds)
            ->get(['property_id', 'building_name']);
    }

    public function updatedSelectedBuilding($propertyId)
    {
        $this->selectedUnit = '';
        $this->selectedBed  = '';
        $this->units        = [];
        $this->beds         = [];

        if ($propertyId) {
            $unitQuery = Unit::where('property_id', $propertyId)
                ->where('manager_id', Auth::id())
                ->orderBy('unit_number');

            // In add/transfer mode, hide fully occupied units.
            if (!$this->isEdit()) {
                $unitQuery->whereHas('beds', function ($query) {
                    $query->where('status', 'Vacant');
                });
            }

            $this->units = $unitQuery->get(['unit_id', 'unit_number']);
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

    // Auto-compute short-term premium when term changes (does NOT affect monthlyRate)
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
        try {
            $this->validate($this->validationRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error');
            throw $e;
        }

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
        $password = PasswordGenerator::generate();
        $photoPath = null;
        $idImagePath = null;
        $createdUser = null;

        try {
            $photoPath = $this->profilePicture
                ? $this->profilePicture->store('profile-photos', 'public')
                : null;

            $idImagePath = $this->governmentIdImage
                ? $this->governmentIdImage->store('government-ids', 'public')
                : null;

            DB::transaction(function () use ($photoPath, $idImagePath, $password, &$createdUser) {
                $createdUser = User::create([
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

                $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

                $lease = Lease::create([
                    'tenant_id'             => $createdUser->user_id,
                    'bed_id'                => $this->selectedBed,
                    'status'                => 'Active',
                    'term'                  => $this->term,
                    'auto_renew'            => $this->autoRenew,
                    'start_date'            => $this->startDate,
                    'end_date'              => $endDate,
                    'contract_rate'         => $this->monthlyRate,
                    'advance_amount'        => $this->monthlyRate,
                    'security_deposit'      => $this->securityDeposit,
                    'move_in'               => $this->startDate,
                    'shift'                 => $this->shift,
                    'monthly_due_date'      => $this->monthlyDueDate,
                    'late_payment_penalty'  => 100,
                    'short_term_premium'    => $this->shortTermPremium,
                    'reservation_fee_paid'  => 0,
                    'early_termination_fee' => 0,
                ]);

                $isPaid = $this->paymentStatus === 'Paid';

                // Main billing (rent)
                $billing = Billing::create([
                    'lease_id'     => $lease->lease_id,
                    'billing_date' => Carbon::parse($this->startDate),
                    'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                    'to_pay'       => $this->monthlyRate + $this->shortTermPremium,
                    'amount'       => $this->monthlyRate + $this->shortTermPremium,
                    'status'       => $this->paymentStatus,
                ]);

                // Unpaid — create a separate deposit billing with no transactions
                $depbilling = Billing::create([
                    'lease_id'     => $lease->lease_id,
                    'billing_date' => Carbon::parse($this->startDate),
                    'next_billing' => Carbon::parse($this->startDate),
                    'to_pay'       => $this->securityDeposit,
                    'amount'       => $this->securityDeposit,
                    'status'       => 'Unpaid',
                ]);

                if ($isPaid) {
                    // Deposit transaction
                    $depTransaction = Transaction::createWithSequenceRetry([
                        'billing_id'       => $depbilling->billing_id,
                        'reference_number' => 'placeholder',
                        'transaction_type' => 'Debit',
                        'category'         => 'Deposit',
                        'transaction_date' => today(),
                        'amount'           => $this->securityDeposit ?? 0,
                    ]);
                    $depTransaction->update([
                        'reference_number' => 'DEP' . now()->format('Ymd') . '-' . str_pad($depTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
                    ]);

                    // Advance transaction — short-term premium (+₱500) added only here
                    $advTransaction = Transaction::createWithSequenceRetry([
                        'billing_id'       => $billing->billing_id,
                        'reference_number' => 'placeholder',
                        'transaction_type' => 'Debit',
                        'category'         => 'Advance',
                        'transaction_date' => today(),
                        'amount'           => ($this->monthlyRate ?? 0) + $this->shortTermPremium,
                    ]);
                    $advTransaction->update([
                        'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
                    ]);
                }

                Bed::where('bed_id', $this->selectedBed)->update(['status' => 'Occupied']);
            });
        } catch (\Throwable $exception) {
            if ($photoPath) {
                $this->deleteStoredImage($photoPath);
            }
            if ($idImagePath) {
                $this->deleteStoredImage($idImagePath);
            }

            Log::error('Failed to save new tenant.', [
                'email' => $this->email,
                'selected_bed' => $this->selectedBed,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        if ($createdUser) {
            $this->attemptWelcomeEmailDelivery($createdUser, $password);
        }

        $this->notifySuccess(
            'Tenant Added Successfully!',
            $this->firstName . ' ' . $this->lastName . ' has been added to ' . $this->selectedBed . '.'
        );

        session()->flash('success', 'Tenant added successfully!');
    }

    private function attemptWelcomeEmailDelivery(User $createdUser, string $password): void
    {
        $notification = new NewAccount($createdUser->email, $password, $createdUser->role);

        $this->logWelcomeEmailPreview($notification, $createdUser);

        $sendGridAttempt = [
            'ok' => false,
            'status' => null,
            'error' => 'No SendGrid attempt data.',
        ];

        try {
            SendGridChannel::resetLastAttempt();
            Notification::send($createdUser, $notification);

            $sendGridAttempt = SendGridChannel::lastAttempt() ?? $sendGridAttempt;
        } catch (\Throwable $exception) {
            $sendGridAttempt = [
                'ok' => false,
                'status' => null,
                'error' => $exception->getMessage(),
            ];

            Log::warning('SendGrid call failed for tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);
        }

        if (($sendGridAttempt['ok'] ?? false) === true) {
            Log::info('Tenant welcome email sent via SendGrid.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'status' => $sendGridAttempt['status'] ?? null,
            ]);

            return;
        }

        Log::warning('Tenant welcome email SendGrid failed; trying SMTPS fallback.', [
            'tenant_id' => $createdUser->user_id,
            'tenant_email' => $createdUser->email,
            'sendgrid_status' => $sendGridAttempt['status'] ?? null,
            'sendgrid_error' => $sendGridAttempt['error'] ?? null,
        ]);

        try {
            Mail::mailer('smtp')
                ->to($createdUser->email)
                ->send(new NewAccountSmtpMail(
                    email: $createdUser->email,
                    password: $password,
                    role: $createdUser->role,
                    firstName: (string) ($createdUser->first_name ?? ''),
                    lastName: (string) ($createdUser->last_name ?? ''),
                ));

            Log::info('Tenant welcome email sent via SMTPS fallback.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'mailer' => 'smtp',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('SMTPS fallback failed for tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);

            $this->notifyWarning(
                'Tenant saved, email retry failed',
                'Email delivery failed due to provider limits. Tenant record was saved successfully.'
            );
        }
    }

    private function logWelcomeEmailPreview(NewAccount $notification, User $createdUser): void
    {
        if (!config('services.sendgrid.preview_logging', false)) {
            return;
        }

        try {
            $mailMessage = $notification->toMail($createdUser);
            
            // Only log if there's an issue rendering the email
            // Don't log the massive HTML/text content - just verify it renders
            if (empty($mailMessage->subject)) {
                Log::warning('Tenant welcome email missing subject.', [
                    'tenant_id' => $createdUser->user_id,
                    'tenant_email' => $createdUser->email,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Error rendering tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);
        }
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

            $carryOverDeposit = $oldSecurityDeposit >= $newSecurityDeposit
                ? $oldSecurityDeposit
                : $newSecurityDeposit;

            $depositShortfall = $oldSecurityDeposit < $newSecurityDeposit
                ? $newSecurityDeposit - $oldSecurityDeposit
                : 0;

            $endDate = Carbon::parse($this->startDate)->addMonths((int) $this->term ?: 6);

            $lease = Lease::create([
                'tenant_id'             => $this->transferFromTenantId,
                'bed_id'                => $this->selectedBed,
                'status'                => 'Active',
                'term'                  => $this->term,
                'auto_renew'            => $this->autoRenew,
                'start_date'            => $this->startDate,
                'end_date'              => $endDate,
                'contract_rate'         => $this->monthlyRate,
                'advance_amount'        => $this->monthlyRate,
                'security_deposit'      => $carryOverDeposit,
                'move_in'               => $this->startDate,
                'shift'                 => $this->shift,
                'monthly_due_date'      => $this->monthlyDueDate,
                'late_payment_penalty'  => 100,
                'short_term_premium'    => $this->shortTermPremium,
                'reservation_fee_paid'  => 0,
                'early_termination_fee' => 0,
            ]);

            $isPaid = $this->paymentStatus === 'Paid';

            // Main billing (rent)
            $billing = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate)->addMonth(),
                'to_pay'       => $this->monthlyRate + $this->shortTermPremium,
                'amount'       => $this->monthlyRate + $this->shortTermPremium,
                'status'       => $this->paymentStatus,
            ]);

            $depbilling = Billing::create([
                'lease_id'     => $lease->lease_id,
                'billing_date' => Carbon::parse($this->startDate),
                'next_billing' => Carbon::parse($this->startDate),
                'to_pay'       => $newSecurityDeposit,
                'amount'       => $newSecurityDeposit,
                'status'       => 'Unpaid',
            ]);

            if ($isPaid) {
                // Advance transaction — short-term premium (+₱500) added only here
                $advTransaction = Transaction::createWithSequenceRetry([
                    'billing_id'       => $billing->billing_id,
                    'reference_number' => 'placeholder',
                    'transaction_type' => 'Debit',
                    'category'         => 'Advance',
                    'transaction_date' => today(),
                    'amount'           => $this->monthlyRate + $this->shortTermPremium,
                ]);
                $advTransaction->update([
                    'reference_number' => 'ADV' . now()->format('Ymd') . '-' . str_pad($advTransaction->transaction_id, 6, '0', STR_PAD_LEFT),
                ]);

                if ($depositShortfall > 0) {
                    $depTransaction = Transaction::createWithSequenceRetry([
                        'billing_id'       => $depbilling->billing_id,
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
            }

            Bed::where('bed_id', $this->selectedBed)->update(['status' => 'Occupied']);
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

            $oldProfileImg = $tenant->profile_img;
            $oldGovernmentIdImage = $tenant->government_id_image;

            $photoPath = $this->profilePicture
                ? $this->profilePicture->store('profile-photos', 'public')
                : $tenant->profile_img;

            $idImagePath = $this->governmentIdImage
                ? $this->governmentIdImage->store('government-ids', 'public')
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
                        'bed_id'                => $this->selectedBed,
                        'term'                  => $this->term,
                        'auto_renew'            => $this->autoRenew,
                        'start_date'            => $this->startDate,
                        'end_date'              => $endDate,
                        'contract_rate'         => $this->monthlyRate,
                        'advance_amount'        => $this->monthlyRate,
                        'security_deposit'      => $this->securityDeposit,
                        'move_in'               => $this->startDate,
                        'shift'                 => $this->shift,
                        'monthly_due_date'      => $this->monthlyDueDate,
                        'late_payment_penalty'  => 100,
                        'short_term_premium'    => $this->shortTermPremium,
                        'reservation_fee_paid'  => 0,
                        'early_termination_fee' => 0,
                    ]);
                }
            }

            DB::afterCommit(function () use ($oldProfileImg, $oldGovernmentIdImage, $photoPath, $idImagePath) {
                if ($this->profilePicture && $oldProfileImg && $oldProfileImg !== $photoPath) {
                    $this->deleteStoredImage($oldProfileImg);
                }

                if ($this->governmentIdImage && $oldGovernmentIdImage && $oldGovernmentIdImage !== $idImagePath) {
                    $this->deleteStoredImage($oldGovernmentIdImage);
                }
            });
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

    protected function stepValidationRules(int $step): array
    {
        if ($this->isTransfer()) {
            return match ($step) {
                1 => [
                    'selectedBuilding' => 'required',
                    'selectedUnit'     => 'required',
                    'selectedBed'      => 'required|exists:beds,bed_id,status,Vacant',
                    'dormType'         => 'required',
                    'term'             => 'required',
                    'startDate'        => 'required|date',
                    'shift'            => 'required',
                ],
                2 => [
                    'monthlyRate'     => 'required|numeric',
                    'securityDeposit' => 'required|numeric',
                    'paymentStatus'   => 'required',
                    'monthlyDueDate'  => 'required',
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
                'permanentAddress'             => 'required|min:5',
                'governmentIdType'             => 'required',
                'governmentIdNumber'           => 'required|min:3',
                'companySchool'                => 'required|min:2',
                'positionCourse'               => 'required|min:2',
                'emergencyContactName'         => 'required|min:2',
                'emergencyContactRelationship' => 'required',
                'emergencyContactNumber'       => 'required|numeric|digits:10',
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
                'monthlyRate'     => 'required|numeric',
                'securityDeposit' => 'required|numeric',
                'paymentStatus'   => 'required',
                'monthlyDueDate'  => 'required',
            ],
            default => [],
        };
    }

    protected function validationRules(): array
    {
        $rules = [
            'selectedBuilding' => 'required',
            'selectedUnit'     => 'required',
            'selectedBed'      => $this->isEdit() ? 'required' : 'required|exists:beds,bed_id,status,Vacant',
            'dormType'         => 'required',
            'term'             => 'required',
            'startDate'        => 'required|date',
            'shift'            => 'required',
            'monthlyRate'      => 'required|numeric',
            'securityDeposit'  => 'required|numeric',
            'paymentStatus'    => 'required',
            'monthlyDueDate'   => 'required',
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

    private function deleteStoredImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            $normalized = ltrim(trim((string) parse_url($path, PHP_URL_PATH) ?: $path), '/');

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

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
