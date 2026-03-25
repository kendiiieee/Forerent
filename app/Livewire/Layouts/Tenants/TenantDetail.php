<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Lease;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Concerns\WithESignature;
use Livewire\Component;
use Livewire\Attributes\On;

class TenantDetail extends Component
{
    use WithESignature;

    public $currentTenantId = null;
    public $currentTenant = null;
    public $viewingTab = 'current';

    // Move-out modal fields
    public $showMoveInContract = false;
    public $showMoveOutContract = false;

    // E-signature fields (move-in)
    public $showSignatureModal = false;
    public $signatureRole = ''; // 'tenant' or 'owner'
    public $tenantSignature = null;
    public $ownerSignature = null;
    public $tenantSignedAt = null;
    public $ownerSignedAt = null;
    public $contractAgreed = false;

    // E-signature fields (move-out) — independent from move-in
    public $showMoveOutSignatureModal = false;
    public $moveOutSignatureRole = '';
    public $moveOutTenantSignature = null;
    public $moveOutOwnerSignature = null;
    public $moveOutTenantSignedAt = null;
    public $moveOutOwnerSignedAt = null;
    public $moveOutContractAgreed = false;

    // Move-out form fields
    public $forwardingAddress = '';
    public $reasonForVacating = '';
    public $depositRefundMethod = '';
    public $depositRefundAccount = '';

    // Move-in inspection form
    public $inspectionChecklist = [];
    public $itemsReceived = [];
    public $inspectionSaved = false;
    public $currentLeaseId = null;

    // Move-out inspection form
    public $moveOutChecklist = [];
    public $itemsReturned = [];
    public $moveOutInspectionSaved = false;

    // Default checklist items
    public const CHECKLIST_ITEMS = [
        'Bed Frame & Mattress / Foam',
        'Cabinet / Wardrobe (doors & locks)',
        'Air Conditioning Unit & Remote',
        'Bathroom Fixtures (shower, toilet, faucet, heater)',
        'Electrical Outlets & Light Switches',
        'Windows, Curtains / Blinds',
        'Walls (stains, cracks, holes)',
        'Floor Condition',
        'Door Lock & Keys',
    ];

    public const RECEIVED_ITEMS = [
        'Unit Key(s)',
        'Building Access Card / Fob',
        'Wi-Fi Password / Credentials',
        'Air Conditioning Remote',
        'Cabinet Key',
    ];

    // Items that should be returned at move-out (same as received items)
    public const RETURNED_ITEMS = [
        'Unit Key(s)',
        'Building Access Card / Fob',
        'Air Conditioning Remote',
        'Cabinet Key',
    ];

    #[On('tenantSelected')]
    public function loadTenant(int $tenantId, string $tab = 'current', ?int $buildingId = null): void
    {
        $this->viewingTab = $tab;
        $this->showMoveInContract = false;
        $this->showMoveOutContract = false;

        $tenant = User::where('user_id', $tenantId)
            ->where('role', 'tenant')
            ->first();

        if (!$tenant) {
            $this->resetTenantData();
            return;
        }

        if ($tab === 'current') {
            $lease = Lease::where('tenant_id', $tenantId)
                ->where('status', 'Active')
                ->latest()
                ->with([
                    'bed.unit.property',
                    'billings' => fn($q) => $q
                        ->latest('billing_date')
                        ->limit(1),
                    'moveInInspections',
                    'moveOutInspections',
                ])
                ->first();
        } else {
            $leaseQuery = Lease::where('tenant_id', $tenantId)
                ->where('status', 'Expired')
                ->with([
                    'bed.unit.property',
                    'billings' => fn($q) => $q
                        ->latest('billing_date')
                        ->limit(1),
                    'moveInInspections',
                    'moveOutInspections',
                ]);

            if ($buildingId) {
                $leaseQuery->whereHas('bed.unit', fn($q) => $q->where('property_id', $buildingId));
            }

            $lease = $leaseQuery->latest()->first();
        }

        $bed      = $lease?->bed;
        $unit     = $bed?->unit;
        $property = $unit?->property;
        $owner    = $property?->owner;
        $billing  = $lease?->billings->first();

        $this->currentTenantId = $tenantId;
        $this->currentLeaseId = $lease?->lease_id;
        $this->currentTenant = [
            'lessor_info' => [
                'business_name'    => $property?->building_name,
                'company_name'     => $owner?->company_school ?? 'ForeRent',
                'address'          => $property?->address,
                'contact'          => $owner?->contact,
                'email'            => $owner?->email,
                'representative'   => $owner ? ($owner->first_name . ' ' . $owner->last_name) : '—',
            ],
            'personal_info' => [
                'first_name'       => $tenant->first_name,
                'last_name'        => $tenant->last_name,
                'gender'           => $tenant->gender,
                'address'          => $property?->address,
                'property'         => $property?->building_name,
                'unit'             => $unit?->unit_number,
                'permanent_address' => $tenant->permanent_address,
                'government_id_type'   => $tenant->government_id_type,
                'government_id_number' => $tenant->government_id_number,
                'government_id_image'  => $tenant->government_id_image,
                'company_school'       => $tenant->company_school,
                'position_course'      => $tenant->position_course,
                'emergency_contact_name'         => $tenant->emergency_contact_name,
                'emergency_contact_relationship' => $tenant->emergency_contact_relationship,
                'emergency_contact_number'       => $tenant->emergency_contact_number,
            ],
            'contact_info' => [
                'contact_number' => $tenant->contact,
                'email'          => $tenant->email,
            ],
            'rent_details' => [
                'bed_number'       => $bed?->bed_number,
                'dorm_type'        => $unit?->occupants,
                'floor'            => $unit?->floor_number,
                'room_type'        => $unit?->room_type,
                'lease_start_date' => $lease?->start_date?->format('Y-m-d'),
                'lease_end_date'   => $lease?->end_date?->format('Y-m-d'),
                'lease_term'       => $lease?->term,
                'shift'            => $lease?->shift,
                'auto_renew'       => $lease?->auto_renew,
            ],
            'move_in_details' => [
                'move_in_date'          => $lease?->move_in?->format('Y-m-d'),
                'monthly_rate'          => $lease?->contract_rate,
                'security_deposit'      => $lease?->security_deposit,
                'payment_status'        => $billing?->status ?? 'No billing',
                'monthly_due_date'      => $lease?->monthly_due_date,
                'late_payment_penalty'  => $lease?->late_payment_penalty,
                'short_term_premium'    => $lease?->short_term_premium,
                'reservation_fee_paid'  => $lease?->reservation_fee_paid,
                'early_termination_fee' => $lease?->early_termination_fee,
            ],
            'move_out_details' => [
                'move_out_date'          => $lease?->move_out?->format('Y-m-d'),
                'forwarding_address'     => $lease?->forwarding_address,
                'reason_for_vacating'    => $lease?->reason_for_vacating,
                'deposit_refund_method'  => $lease?->deposit_refund_method,
                'deposit_refund_account' => $lease?->deposit_refund_account,
            ],
            'signature_info' => [
                'tenant_signature'      => $lease?->tenant_signature,
                'tenant_signed_at'      => $lease?->tenant_signed_at?->format('M d, Y h:i A'),
                'owner_signature'       => $lease?->owner_signature,
                'owner_signed_at'       => $lease?->owner_signed_at?->format('M d, Y h:i A'),
                'contract_agreed'       => (bool) $lease?->contract_agreed,
                'signed_contract_path'  => $lease?->signed_contract_path,
            ],
        ];

        // Load move-in signature state
        $this->tenantSignature = $lease?->tenant_signature;
        $this->ownerSignature = $lease?->owner_signature;
        $this->tenantSignedAt = $lease?->tenant_signed_at?->format('M d, Y h:i A');
        $this->ownerSignedAt = $lease?->owner_signed_at?->format('M d, Y h:i A');
        $this->contractAgreed = (bool) $lease?->contract_agreed;

        // Load move-out signature state (independent)
        $this->moveOutTenantSignature = $lease?->moveout_tenant_signature;
        $this->moveOutOwnerSignature = $lease?->moveout_owner_signature;
        $this->moveOutTenantSignedAt = $lease?->moveout_tenant_signed_at?->format('M d, Y h:i A');
        $this->moveOutOwnerSignedAt = $lease?->moveout_owner_signed_at?->format('M d, Y h:i A');
        $this->moveOutContractAgreed = (bool) $lease?->moveout_contract_agreed;

        $this->loadInspectionData($lease);
        $this->loadMoveOutInspectionData($lease);
    }

    private function loadInspectionData($lease): void
    {
        $existingInspections = $lease?->moveInInspections ?? collect();

        $savedChecklist = $existingInspections->where('type', 'checklist');
        $savedItems = $existingInspections->where('type', 'item_received');

        $this->inspectionSaved = $savedChecklist->isNotEmpty() || $savedItems->isNotEmpty();

        // Build checklist array
        $this->inspectionChecklist = [];
        foreach (self::CHECKLIST_ITEMS as $item) {
            $saved = $savedChecklist->firstWhere('item_name', $item);
            $this->inspectionChecklist[] = [
                'item_name' => $item,
                'condition' => $saved?->condition ?? '',
                'remarks'   => $saved?->remarks ?? '',
            ];
        }

        // Build items received array
        $this->itemsReceived = [];
        foreach (self::RECEIVED_ITEMS as $item) {
            $saved = $savedItems->firstWhere('item_name', $item);
            $this->itemsReceived[] = [
                'item_name'        => $item,
                'quantity'         => $saved?->quantity ?? '',
                'condition'        => $saved?->remarks ?? '',
                'tenant_confirmed' => $saved?->tenant_confirmed ?? false,
            ];
        }
    }

    public function updatedInspectionChecklist($value, $key): void
    {
        // key is like "0.condition" — clear error when user selects a condition
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'condition') {
            $this->resetErrorBag("inspectionChecklist.{$parts[0]}.condition");
        }
    }

    public function updatedItemsReceived($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'quantity') {
            // Strip non-numeric characters
            $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
            $this->itemsReceived[$index]['quantity'] = $cleaned;

            $this->resetErrorBag("itemsReceived.{$index}.quantity");
            if ($cleaned === '' || $cleaned === null) {
                $this->addError("itemsReceived.{$index}.quantity", 'Required');
            } elseif ((int) $cleaned < 1) {
                $this->addError("itemsReceived.{$index}.quantity", 'Min 1');
            }
        }

        if ($field === 'condition') {
            $this->resetErrorBag("itemsReceived.{$index}.condition");
            if (empty(trim((string) $value))) {
                $this->addError("itemsReceived.{$index}.condition", 'Required');
            }
        }
    }

    public function saveInspection(): void
    {
        if (!$this->currentLeaseId) return;

        // Validate all checklist items have a condition selected
        $errors = [];
        foreach ($this->inspectionChecklist as $index => $item) {
            if (empty($item['condition'])) {
                $errors["inspectionChecklist.{$index}.condition"] = "Select a condition for \"{$item['item_name']}\".";
            }
        }

        // Validate all items received have quantity (numeric) and condition
        foreach ($this->itemsReceived as $index => $item) {
            if ($item['quantity'] === '' || $item['quantity'] === null) {
                $errors["itemsReceived.{$index}.quantity"] = "Enter quantity for \"{$item['item_name']}\".";
            } elseif (!is_numeric($item['quantity']) || (int) $item['quantity'] < 1) {
                $errors["itemsReceived.{$index}.quantity"] = "Quantity must be at least 1.";
            }
            if (empty(trim($item['condition'] ?? ''))) {
                $errors["itemsReceived.{$index}.condition"] = "Enter condition for \"{$item['item_name']}\".";
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->addError($key, $message);
            }
            return;
        }

        // Delete existing inspection data for this lease
        MoveInInspection::where('lease_id', $this->currentLeaseId)->delete();

        // Save checklist items
        foreach ($this->inspectionChecklist as $item) {
            if (!empty($item['condition'])) {
                MoveInInspection::create([
                    'lease_id'  => $this->currentLeaseId,
                    'type'      => 'checklist',
                    'item_name' => $item['item_name'],
                    'condition' => $item['condition'],
                    'remarks'   => $item['remarks'] ?? null,
                ]);
            }
        }

        // Save items received
        foreach ($this->itemsReceived as $item) {
            if (!empty($item['quantity']) || !empty($item['condition']) || $item['tenant_confirmed']) {
                MoveInInspection::create([
                    'lease_id'         => $this->currentLeaseId,
                    'type'             => 'item_received',
                    'item_name'        => $item['item_name'],
                    'quantity'         => $item['quantity'] ?: null,
                    'remarks'          => $item['condition'] ?? null,
                    'tenant_confirmed' => $item['tenant_confirmed'] ?? false,
                ]);
            }
        }

        $this->inspectionSaved = true;
        $this->dispatch('inspection-saved');
        $this->dispatch('notify', type: 'success', title: 'Inspection Saved', description: 'Move-in inspection data has been saved to the contract.');
    }

    public function cancelInspection(): void
    {
        // Reload from database
        if ($this->currentLeaseId) {
            $lease = Lease::with('moveInInspections')->find($this->currentLeaseId);
            $this->loadInspectionData($lease);
        }
        $this->dispatch('inspection-cancelled');
    }

    // ===== MOVE-OUT INSPECTION METHODS =====

    private function loadMoveOutInspectionData($lease): void
    {
        $existingInspections = $lease?->moveOutInspections ?? collect();

        $savedChecklist = $existingInspections->where('type', 'checklist');
        $savedItems = $existingInspections->where('type', 'item_returned');

        $this->moveOutInspectionSaved = $savedChecklist->isNotEmpty() || $savedItems->isNotEmpty();

        // Build checklist array (same items as move-in for comparison)
        $this->moveOutChecklist = [];
        foreach (self::CHECKLIST_ITEMS as $item) {
            $saved = $savedChecklist->firstWhere('item_name', $item);
            $this->moveOutChecklist[] = [
                'item_name' => $item,
                'condition' => $saved?->condition ?? '',
                'remarks'   => $saved?->remarks ?? '',
            ];
        }

        // Build items returned array
        $this->itemsReturned = [];
        foreach (self::RETURNED_ITEMS as $item) {
            $saved = $savedItems->firstWhere('item_name', $item);
            $this->itemsReturned[] = [
                'item_name'        => $item,
                'quantity'         => $saved?->quantity ?? '',
                'condition'        => $saved?->remarks ?? '',
                'tenant_confirmed' => $saved?->tenant_confirmed ?? false,
            ];
        }
    }

    public function updatedMoveOutChecklist($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'condition') {
            $this->resetErrorBag("moveOutChecklist.{$parts[0]}.condition");
        }
    }

    public function updatedItemsReturned($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'quantity') {
            $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
            $this->itemsReturned[$index]['quantity'] = $cleaned;

            $this->resetErrorBag("itemsReturned.{$index}.quantity");
            if ($cleaned === '' || $cleaned === null) {
                $this->addError("itemsReturned.{$index}.quantity", 'Required');
            } elseif ((int) $cleaned < 1) {
                $this->addError("itemsReturned.{$index}.quantity", 'Min 1');
            }
        }

        if ($field === 'condition') {
            $this->resetErrorBag("itemsReturned.{$index}.condition");
            if (empty(trim((string) $value))) {
                $this->addError("itemsReturned.{$index}.condition", 'Required');
            }
        }
    }

    public function saveMoveOutInspection(): void
    {
        if (!$this->currentLeaseId) return;

        // Validate all checklist items have a condition selected
        $errors = [];
        foreach ($this->moveOutChecklist as $index => $item) {
            if (empty($item['condition'])) {
                $errors["moveOutChecklist.{$index}.condition"] = "Select a condition for \"{$item['item_name']}\".";
            }
        }

        // Validate all items returned have quantity and condition
        foreach ($this->itemsReturned as $index => $item) {
            if ($item['quantity'] === '' || $item['quantity'] === null) {
                $errors["itemsReturned.{$index}.quantity"] = "Enter quantity for \"{$item['item_name']}\".";
            } elseif (!is_numeric($item['quantity']) || (int) $item['quantity'] < 1) {
                $errors["itemsReturned.{$index}.quantity"] = "Quantity must be at least 1.";
            }
            if (empty(trim($item['condition'] ?? ''))) {
                $errors["itemsReturned.{$index}.condition"] = "Enter condition for \"{$item['item_name']}\".";
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->addError($key, $message);
            }
            return;
        }

        // Delete existing move-out inspection data for this lease
        MoveOutInspection::where('lease_id', $this->currentLeaseId)->delete();

        // Save checklist items
        foreach ($this->moveOutChecklist as $item) {
            if (!empty($item['condition'])) {
                MoveOutInspection::create([
                    'lease_id'  => $this->currentLeaseId,
                    'type'      => 'checklist',
                    'item_name' => $item['item_name'],
                    'condition' => $item['condition'],
                    'remarks'   => $item['remarks'] ?? null,
                ]);
            }
        }

        // Save items returned
        foreach ($this->itemsReturned as $item) {
            if (!empty($item['quantity']) || !empty($item['condition']) || $item['tenant_confirmed']) {
                MoveOutInspection::create([
                    'lease_id'         => $this->currentLeaseId,
                    'type'             => 'item_returned',
                    'item_name'        => $item['item_name'],
                    'quantity'         => $item['quantity'] ?: null,
                    'remarks'          => $item['condition'] ?? null,
                    'tenant_confirmed' => $item['tenant_confirmed'] ?? false,
                ]);
            }
        }

        $this->moveOutInspectionSaved = true;
        $this->dispatch('moveout-inspection-saved');
        $this->dispatch('notify', type: 'success', title: 'Inspection Saved', description: 'Move-out inspection data has been saved.');
    }

    public function cancelMoveOutInspection(): void
    {
        if ($this->currentLeaseId) {
            $lease = Lease::with('moveOutInspections')->find($this->currentLeaseId);
            $this->loadMoveOutInspectionData($lease);
        }
        $this->dispatch('moveout-inspection-cancelled');
    }

    private function resetTenantData(): void
    {
        $this->currentTenantId = null;
        $this->currentTenant = null;
        $this->currentLeaseId = null;
        $this->inspectionChecklist = [];
        $this->itemsReceived = [];
        $this->inspectionSaved = false;
        $this->moveOutChecklist = [];
        $this->itemsReturned = [];
        $this->moveOutInspectionSaved = false;
        $this->moveOutTenantSignature = null;
        $this->moveOutOwnerSignature = null;
        $this->moveOutTenantSignedAt = null;
        $this->moveOutOwnerSignedAt = null;
        $this->moveOutContractAgreed = false;
    }

    public function editTenant(): void
    {
        if ($this->currentTenantId) {
            $this->dispatch('open-edit-tenant-modal', tenantId: $this->currentTenantId);
        }
    }

    public function transferTenant(): void
    {
        if ($this->currentTenantId) {
            $this->dispatch('open-transfer-tenant-modal', tenantId: $this->currentTenantId);
        }
    }

    public function moveOutTenant(): void
    {
        if ($this->currentTenantId) {
            $this->dispatch('open-modal', 'move-out-confirmation');
        }
    }

    public function confirmMoveOut(): void
    {
        if (!$this->currentTenantId) return;

        $lease = Lease::where('tenant_id', $this->currentTenantId)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if ($lease) {
            $lease->update([
                'status'   => 'Expired',
                'move_out' => \Carbon\Carbon::today(),
                'end_date' => \Carbon\Carbon::today(),
            ]);

            \App\Models\Bed::where('bed_id', $lease->bed_id)
                ->update(['status' => 'Vacant']);
        }

        $this->dispatch('refresh-tenant-list');
        $this->dispatch('close-modal', 'move-out-confirmation');
        $this->resetTenantData();
        session()->flash('success', 'Tenant moved out successfully!');
    }

    public function openMoveInContract(): void
    {
        $this->showMoveInContract = true;
        $this->showMoveOutContract = false;
    }

    public function closeMoveInContract(): void
    {
        $this->showMoveInContract = false;
    }

    public function openMoveOutContract(): void
    {
        $this->showMoveOutContract = true;
        $this->showMoveInContract = false;
    }

    public function closeMoveOutContract(): void
    {
        $this->showMoveOutContract = false;
    }

    public function openSignatureModal(string $role): void
    {
        $this->signatureRole = $role;
        $this->showSignatureModal = true;
    }

    public function closeSignatureModal(): void
    {
        $this->showSignatureModal = false;
        $this->signatureRole = '';
    }

    public function saveSignature(string $signatureData): void
    {
        if (!$this->currentLeaseId || !$this->signatureRole) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, $this->signatureRole, 'movein');

        if ($this->signatureRole === 'tenant') {
            $this->tenantSignature = $result['signature'];
            $this->tenantSignedAt = $result['signedAt'];
        } else {
            $this->ownerSignature = $result['signature'];
            $this->ownerSignedAt = $result['signedAt'];
        }
        $this->contractAgreed = $result['agreed'];

        // If both signatures exist, generate PDF
        if ($result['agreed']) {
            $lease->refresh();
            $this->generateSignedPdf($lease);
        }

        // Update signature_info in currentTenant
        $lease->refresh();
        $this->currentTenant['signature_info'] = [
            'tenant_signature'      => $lease->tenant_signature,
            'tenant_signed_at'      => $lease->tenant_signed_at?->format('M d, Y h:i A'),
            'owner_signature'       => $lease->owner_signature,
            'owner_signed_at'       => $lease->owner_signed_at?->format('M d, Y h:i A'),
            'contract_agreed'       => (bool) $lease->contract_agreed,
            'signed_contract_path'  => $lease->signed_contract_path,
        ];

        $this->closeSignatureModal();
        $this->dispatch('signature-saved');
    }

    private function generateSignedPdf(Lease $lease): void
    {
        $lease->load(['tenant', 'bed.unit.property']);

        $tenant = $lease->tenant;
        $bed = $lease->bed;
        $unit = $bed?->unit;
        $property = $unit?->property;
        $owner = $property?->owner;

        $tenantSigPath = Storage::disk('public')->path($lease->tenant_signature);
        $ownerSigPath = Storage::disk('public')->path($lease->owner_signature);

        $data = [
            'tenant' => $this->currentTenant,
            'lessor' => $this->currentTenant['lessor_info'],
            't' => $this->currentTenant,
            'tenantSignatureBase64' => 'data:image/png;base64,' . base64_encode(file_get_contents($tenantSigPath)),
            'ownerSignatureBase64'  => 'data:image/png;base64,' . base64_encode(file_get_contents($ownerSigPath)),
            'tenantSignedAt' => $lease->tenant_signed_at->format('M d, Y'),
            'ownerSignedAt'  => $lease->owner_signed_at->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('pdf.move-in-contract', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $pdfPath = "contracts/lease_{$lease->lease_id}_signed_" . time() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Delete old signed PDF if exists
        if ($lease->signed_contract_path) {
            Storage::disk('public')->delete($lease->signed_contract_path);
        }

        $lease->update(['signed_contract_path' => $pdfPath]);
    }

    public function downloadSignedContract()
    {
        if (!$this->currentLeaseId) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease?->signed_contract_path) return;

        return Storage::disk('public')->download(
            $lease->signed_contract_path,
            'Move-In-Contract-' . ($this->currentTenant['personal_info']['last_name'] ?? 'Tenant') . '.pdf'
        );
    }

    // ===== MOVE-OUT E-SIGNATURE METHODS (independent from move-in) =====

    public function openMoveOutSignatureModal(string $role): void
    {
        $this->moveOutSignatureRole = $role;
        $this->showMoveOutSignatureModal = true;
    }

    public function closeMoveOutSignatureModal(): void
    {
        $this->showMoveOutSignatureModal = false;
        $this->moveOutSignatureRole = '';
    }

    public function saveMoveOutSignature(string $signatureData): void
    {
        if (!$this->currentLeaseId || !$this->moveOutSignatureRole) return;

        $lease = Lease::find($this->currentLeaseId);
        if (!$lease) return;

        $result = $this->saveLeaseSignature($lease, $signatureData, $this->moveOutSignatureRole, 'moveout');

        if ($this->moveOutSignatureRole === 'tenant') {
            $this->moveOutTenantSignature = $result['signature'];
            $this->moveOutTenantSignedAt = $result['signedAt'];
        } else {
            $this->moveOutOwnerSignature = $result['signature'];
            $this->moveOutOwnerSignedAt = $result['signedAt'];
        }
        $this->moveOutContractAgreed = $result['agreed'];

        $this->closeMoveOutSignatureModal();
        $this->dispatch('moveout-signature-saved');
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-detail');
    }
}
