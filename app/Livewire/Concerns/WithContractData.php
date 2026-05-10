<?php

namespace App\Livewire\Concerns;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\MoveInInspection;
use App\Models\MoveOutInspection;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Shared contract data builder, signature state, notification helpers,
 * and inspection load/validate/save logic used by both
 * TenantDetail (manager) and TenantDashboardOverview (tenant).
 */
trait WithContractData
{
    // =========================================================================
    // A — Shared tenant/contract data array builder
    // =========================================================================

    /**
     * Build the full contract data array from a tenant, lease, and related models.
     * Replaces the duplicated 70+ line array in both components.
     */
    protected function buildContractDataArray(User $tenant, ?Lease $lease): array
    {
        $bed      = $lease?->bed;
        $unit     = $bed?->unit;
        $property = $unit?->property;
        $owner    = $property?->owner;
        $manager  = $unit?->manager_id ? User::find($unit->manager_id) : null;
        $billing  = $lease?->billings->first();

        return [
            'lessor_info' => [
                'business_name'    => $property?->building_name,
                'company_name'     => $owner?->company_school ?? 'ForeRent',
                'address'          => $property?->address,
                'contact'          => $owner?->contact,
                'email'            => $owner?->email,
                'representative'   => $owner ? ($owner->first_name . ' ' . $owner->last_name) : '—',
                'government_id_type'   => $owner?->government_id_type,
                'government_id_number' => $owner?->government_id_number,
            ],
            'manager_info' => [
                'name' => $manager ? ($manager->first_name . ' ' . $manager->last_name) : 'Unit Manager',
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
                'move_in_date'           => $lease?->move_in?->format('Y-m-d'),
                'monthly_rate'           => $lease?->contract_rate,
                'security_deposit'       => $lease?->security_deposit,
                'advance_amount'         => $lease?->advance_amount,
                'payment_status'         => $billing?->status ?? 'No billing',
                'monthly_due_date'       => $lease?->monthly_due_date,
                'late_payment_penalty'   => $lease?->late_payment_penalty,
                'short_term_premium'     => $lease?->short_term_premium ?? 0,
                'reservation_fee_paid'   => $lease?->reservation_fee_paid,
                'early_termination_fee'  => $lease?->early_termination_fee,
                'move_in_payment_method' => $lease?->move_in_payment_method,
                'move_in_or_number'      => $lease?->move_in_or_number,
                'move_in_receipt_image'  => $lease?->move_in_receipt_image,
            ],
            'move_out_details' => [
                'move_out_date'          => $lease?->move_out?->format('Y-m-d'),
                'move_out_initiated_at'  => $lease?->move_out_initiated_at?->format('Y-m-d H:i'),
                'forwarding_address'     => $lease?->forwarding_address,
                'reason_for_vacating'    => $lease?->reason_for_vacating,
                'deposit_refund_method'  => $lease?->deposit_refund_method,
                'deposit_refund_account' => $lease?->deposit_refund_account,
            ],
            'termination_notice' => (function () use ($lease) {
                $vacateBy = $lease?->vacate_by_date;
                $today    = now()->startOfDay();
                $isOverdue = $vacateBy && $today->gt($vacateBy->startOfDay());

                return [
                    'issued_at'      => $lease?->termination_notice_issued_at?->format('M d, Y'),
                    'issued_at_iso'  => $lease?->termination_notice_issued_at?->toDateString(),
                    'vacate_by'      => $vacateBy?->format('M d, Y'),
                    'vacate_by_iso'  => $vacateBy?->toDateString(),
                    'days_remaining' => $vacateBy
                        ? ($isOverdue ? 0 : (int) $today->diffInDays($vacateBy->startOfDay()))
                        : null,
                    'is_overdue'     => $isOverdue,
                    'violation_id'   => $lease?->termination_notice_violation_id,
                    'has_pdf'        => (bool) $lease?->termination_notice_path,
                    'early_vacate'   => [
                        'status'         => $lease?->early_vacate_status, // null | pending_tenant | accepted | declined
                        'proposed_date'  => $lease?->early_vacate_proposed_date?->format('M d, Y'),
                        'proposed_date_iso' => $lease?->early_vacate_proposed_date?->toDateString(),
                        'reason'         => $lease?->early_vacate_request_reason,
                        'requested_at'   => $lease?->early_vacate_requested_at?->format('M d, Y'),
                        'responded_at'   => $lease?->early_vacate_responded_at?->format('M d, Y'),
                        'response_note'  => $lease?->early_vacate_response_note,
                    ],
                ];
            })(),
            'signature_info' => [
                'tenant_signature'      => $lease?->tenant_signature,
                'tenant_signed_at'      => $lease?->tenant_signed_at?->format('M d, Y h:i A'),
                'owner_signature'       => $lease?->owner_signature,
                'owner_signed_at'       => $lease?->owner_signed_at?->format('M d, Y h:i A'),
                'manager_signature'     => $lease?->manager_signature,
                'manager_signed_at'     => $lease?->manager_signed_at?->format('M d, Y h:i A'),
                'contract_agreed'       => (bool) $lease?->contract_agreed,
                'signed_contract_path'  => $lease?->signed_contract_path,
            ],
            'contract_status' => $lease?->contract_status ?? 'draft',
            'approval' => [
                'status'           => $lease?->approval_status ?? 'approved',
                'approved_at'      => $lease?->approved_at?->format('M d, Y h:i A'),
                'rejection_reason' => $lease?->rejection_reason,
            ],
            'contract_settings' => $property?->contract_settings ?? [],
            'deposit_refund' => [
                'amount' => $lease?->deposit_refund_amount,
                'deductions' => $lease?->deposit_deductions,
                'interest_earned' => $lease?->deposit_interest_amount,
            ],
            'outstanding_balances' => $this->buildOutstandingBalances($lease),
            'eligibility' => [
                'status'              => $tenant->rental_eligibility ?? 'eligible',
                'notes'                => $tenant->eligibility_notes,
                'changed_at'           => $tenant->eligibility_changed_at?->format('M d, Y'),
                'is_blocked'           => $tenant->isBlockedFromRenting(),
                'termination_reason'   => $lease?->termination_reason,
            ],
        ];
    }

    // =========================================================================
    // E — Shared signature state loading
    // =========================================================================

    /**
     * Load move-in and move-out signature state from a lease into component properties.
     * Both components declare these same public properties — this method populates them.
     */
    protected function loadSignatureState(?Lease $lease): void
    {
        // Move-in
        $this->tenantSignature = $lease?->tenant_signature;
        $this->ownerSignature = $lease?->owner_signature;
        $this->managerSignature = $lease?->manager_signature;
        $this->tenantSignedAt = $lease?->tenant_signed_at?->format('M d, Y h:i A');
        $this->ownerSignedAt = $lease?->owner_signed_at?->format('M d, Y h:i A');
        $this->managerSignedAt = $lease?->manager_signed_at?->format('M d, Y h:i A');
        $this->contractAgreed = (bool) $lease?->contract_agreed;

        // Move-out
        $this->moveOutTenantSignature = $lease?->moveout_tenant_signature;
        $this->moveOutOwnerSignature = $lease?->moveout_owner_signature;
        $this->moveOutManagerSignature = $lease?->moveout_manager_signature;
        $this->moveOutTenantSignedAt = $lease?->moveout_tenant_signed_at?->format('M d, Y h:i A');
        $this->moveOutOwnerSignedAt = $lease?->moveout_owner_signed_at?->format('M d, Y h:i A');
        $this->moveOutManagerSignedAt = $lease?->moveout_manager_signed_at?->format('M d, Y h:i A');
        $this->moveOutContractAgreed = (bool) $lease?->moveout_contract_agreed;
    }

    // =========================================================================
    // C — Outstanding balances builder for move-out contract
    // =========================================================================

    /**
     * Build a structured outstanding balances array for the move-out contract.
     * Pulls real data from billings and billing items.
     */
    protected function buildOutstandingBalances(?Lease $lease): array
    {
        if (!$lease) return [];

        $balances = [];

        // Unpaid rent (from billing items of unpaid billings)
        $unpaidBillings = Billing::where('lease_id', $lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->with('items')
            ->get();

        $grouped = [
            'rent'          => ['charge' => 'Unpaid Monthly Rent',       'amount' => 0, 'periods' => []],
            'electricity'   => ['charge' => 'Unpaid Electricity Share',  'amount' => 0, 'periods' => []],
            'water'         => ['charge' => 'Unpaid Water Share',        'amount' => 0, 'periods' => []],
            'late_fee'      => ['charge' => 'Late Payment Fees',         'amount' => 0, 'periods' => []],
            'violation_fee' => ['charge' => 'Violation Fines',           'amount' => 0, 'periods' => []],
            'other'         => ['charge' => 'Other Charges',             'amount' => 0, 'periods' => []],
        ];

        foreach ($unpaidBillings as $billing) {
            $billingPeriod = $billing->billing_date?->format('M Y') ?? '';

            foreach ($billing->items as $item) {
                $key = match ($item->charge_type) {
                    'advance', 'rent' => 'rent',
                    'electricity' => 'electricity',
                    'water' => 'water',
                    'late_fee' => 'late_fee',
                    'violation_fee' => 'violation_fee',
                    default => 'other',
                };
                $grouped[$key]['amount'] += (float) $item->amount;
                if ($billingPeriod && !in_array($billingPeriod, $grouped[$key]['periods'])) {
                    $grouped[$key]['periods'][] = $billingPeriod;
                }
            }
        }

        foreach ($grouped as $group) {
            if ($group['amount'] > 0) {
                $balances[] = [
                    'charge' => $group['charge'],
                    'period' => implode(', ', $group['periods']) ?: '—',
                    'amount' => $group['amount'],
                ];
            }
        }

        return $balances;
    }

    // =========================================================================
    // D — Shared notification helpers
    // =========================================================================

    /**
     * Find the manager ID for a lease (via bed → unit → manager_id).
     */
    protected function findManagerIdForLease(Lease $lease): ?int
    {
        return DB::table('beds')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('beds.bed_id', $lease->bed_id)
            ->value('units.manager_id');
    }

    /**
     * Find the owner ID for a lease (via bed → unit → property → owner_id).
     */
    protected function findOwnerIdForLease(Lease $lease): ?int
    {
        return DB::table('beds')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->where('beds.bed_id', $lease->bed_id)
            ->value('properties.owner_id');
    }

    /**
     * Notify that the owner has signed — next is manager.
     */
    protected function notifyManagerOfOwnerSign(Lease $lease, string $contractType): void
    {
        $managerId = $this->findManagerIdForLease($lease);
        if (!$managerId) return;

        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        Notification::create([
            'user_id' => $managerId,
            'type' => 'contract_signed',
            'title' => 'Contract Signed by Owner',
            'message' => 'The property owner has signed the ' . $label . '. Please sign as witness.',
            'link' => '/manager/tenant',
        ]);
    }

    /**
     * Notify that the manager (witness) has signed — next is tenant.
     */
    protected function notifyTenantOfManagerSign(Lease $lease, string $contractType): void
    {
        if (!$lease->tenant_id) return;

        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        Notification::create([
            'user_id' => $lease->tenant_id,
            'type' => 'contract_signed',
            'title' => 'Contract Ready for Your Signature',
            'message' => 'The owner and manager have signed the ' . $label . '. Please review and sign.',
            'link' => '/tenant?tab=inspection',
        ]);
    }

    /**
     * Notify the manager and owner that the tenant signed a contract.
     */
    protected function notifyManagerOfSign(Lease $lease, string $contractType): void
    {
        $managerId = $this->findManagerIdForLease($lease);
        $ownerId = $this->findOwnerIdForLease($lease);
        $user = \Illuminate\Support\Facades\Auth::user();
        $label = $contractType === 'move-out' ? 'move-out contract' : 'contract';

        $notifyIds = array_filter(array_unique([$managerId, $ownerId]));

        foreach ($notifyIds as $id) {
            Notification::create([
                'user_id' => $id,
                'type' => 'contract_signed',
                'title' => 'Contract Signed by Tenant',
                'message' => $user->first_name . ' ' . $user->last_name . ' has read and signed the ' . $label . '.',
                'link' => '/manager/tenant',
            ]);
        }
    }

    /**
     * Notify the tenant that the owner signed a contract (kept for backward compat).
     */
    protected function notifyTenantOfSign(Lease $lease, string $contractType): void
    {
        if (!$lease->tenant_id) return;

        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        Notification::create([
            'user_id' => $lease->tenant_id,
            'type' => 'contract_signed',
            'title' => 'Contract Signed by Lessor',
            'message' => 'The lessor/authorized representative has signed your ' . $label . '. Please review and sign.',
            'link' => '/tenant?tab=inspection',
        ]);
    }

    /**
     * Inform the tenant that the owner has signed and the signing chain has begun.
     * Sent in addition to notifyManagerOfOwnerSign so all roles see progress.
     */
    protected function notifyTenantOfOwnerSignProgress(Lease $lease, string $contractType): void
    {
        if (!$lease->tenant_id) return;

        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        Notification::create([
            'user_id' => $lease->tenant_id,
            'type' => 'contract_signed',
            'title' => 'Owner Signed Your Contract',
            'message' => 'The property owner has signed your ' . $label . '. The unit manager will sign next as witness, then it will be your turn.',
            'link' => '/tenant?tab=inspection',
        ]);
    }

    /**
     * Inform the owner that the manager has witnessed and the chain has advanced.
     */
    protected function notifyOwnerOfManagerSignProgress(Lease $lease, string $contractType): void
    {
        $ownerId = $this->findOwnerIdForLease($lease);
        if (!$ownerId) return;

        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        Notification::create([
            'user_id' => $ownerId,
            'type' => 'contract_signed',
            'title' => 'Witness Signed the Contract',
            'message' => 'The unit manager has signed the ' . $label . ' as witness. The tenant has been notified to review and sign.',
            'link' => '/landlord/tenant',
        ]);
    }

    /**
     * Notify all three parties (owner, manager, tenant) that the contract has been
     * fully executed. Used after the third signature lands.
     */
    protected function notifyAllOfContractExecuted(Lease $lease, string $contractType): void
    {
        $label = $contractType === 'move-out' ? 'move-out contract' : 'move-in contract';

        $recipients = [
            $lease->tenant_id                    => '/tenant?tab=inspection',
            $this->findManagerIdForLease($lease) => '/manager/tenant',
            $this->findOwnerIdForLease($lease)   => '/landlord/tenant',
        ];

        foreach ($recipients as $userId => $link) {
            if (!$userId) continue;

            Notification::create([
                'user_id' => $userId,
                'type' => 'contract_executed',
                'title' => 'Contract Fully Executed',
                'message' => 'The ' . $label . ' has been signed by all parties (owner, manager, tenant) and is now active. The signed copy is available for download.',
                'link' => $link,
            ]);
        }
    }

    // =========================================================================
    // B — Shared inspection load/validate/save logic
    // =========================================================================

    /**
     * Load inspection data from DB into checklist and items arrays.
     *
     * @param string $checklistProp  Component property name for checklist (e.g. 'inspectionChecklist')
     * @param string $itemsProp      Component property name for items (e.g. 'itemsReceived')
     * @param string $savedProp      Component property name for saved flag (e.g. 'inspectionSaved')
     * @param string $itemType       DB type value for items ('item_received' or 'item_returned')
     * @param array  $itemsList      Constant array of item names to load
     */
    protected function loadInspection(
        $lease,
        string $relation,
        string $checklistProp,
        string $itemsProp,
        string $savedProp,
        string $itemType,
        array $itemsList
    ): void {
        $existingInspections = $lease?->$relation ?? collect();

        $savedChecklist = $existingInspections->where('type', 'checklist');
        $savedItems = $existingInspections->where('type', $itemType);

        $this->$savedProp = $savedChecklist->isNotEmpty() || $savedItems->isNotEmpty();

        $checklist = [];
        foreach (InspectionConfig::CHECKLIST_ITEMS as $item) {
            $saved = $savedChecklist->firstWhere('item_name', $item);
            $checklist[] = [
                'item_name'          => $item,
                'condition'          => $saved?->condition ?? '',
                'remarks'            => $saved?->remarks ?? '',
                'repair_cost'        => $saved?->repair_cost ?? '',
                'id'                 => $saved?->id,
                'dispute_status'     => $saved?->dispute_status ?? 'none',
                'dispute_remarks'    => $saved?->dispute_remarks,
                'resolution_remarks' => $saved?->resolution_remarks,
            ];
        }
        $this->$checklistProp = $checklist;

        $items = [];
        $isMoveOut = $itemType === 'item_returned';

        // For move-out items, the issued quantity should always come from the move-in
        // inspection (write-once at move-in). This makes the "All / None / Partial"
        // control work correctly even before any move-out record has been saved.
        $moveInQuantities = collect();
        if ($isMoveOut && $lease) {
            $moveInQuantities = $lease->moveInInspections
                ->where('type', 'item_received')
                ->keyBy('item_name')
                ->map(fn($i) => (int) ($i->quantity ?? 0));
        }

        foreach ($itemsList as $item) {
            $saved = $savedItems->firstWhere('item_name', $item);
            $issuedQty = $isMoveOut
                ? ($saved?->quantity ?? $moveInQuantities->get($item) ?? 1)
                : ($saved?->quantity ?? '');

            $entry = [
                'item_name'          => $item,
                'quantity'           => $issuedQty,
                'condition'          => $saved?->remarks ?? '',
                'tenant_confirmed'   => $saved?->tenant_confirmed ?? false,
                'id'                 => $saved?->id,
                'dispute_status'     => $saved?->dispute_status ?? 'none',
                'dispute_remarks'    => $saved?->dispute_remarks,
                'resolution_remarks' => $saved?->resolution_remarks,
            ];
            if ($isMoveOut) {
                $entry['is_returned'] = $saved?->is_returned ?? false;
                // Default to 0 (None) so the segmented control has a definite starting state.
                $entry['quantity_returned'] = $saved?->quantity_returned ?? 0;
                $entry['replacement_cost'] = $saved?->replacement_cost ?? '';
            }
            $items[] = $entry;
        }
        $this->$itemsProp = $items;
    }

    /**
     * Validate checklist and items arrays. Returns errors array (empty = valid).
     */
    protected function validateInspection(array $checklist, string $checklistKey, array $items, string $itemsKey): array
    {
        $errors = [];

        foreach ($checklist as $index => $item) {
            if (empty($item['condition'])) {
                $errors["{$checklistKey}.{$index}.condition"] = "Select a condition for \"{$item['item_name']}\".";
            }
        }

        foreach ($items as $index => $item) {
            if ($item['quantity'] === '' || $item['quantity'] === null) {
                $errors["{$itemsKey}.{$index}.quantity"] = "Enter quantity for \"{$item['item_name']}\".";
            } elseif (!is_numeric($item['quantity']) || (int) $item['quantity'] < 1) {
                $errors["{$itemsKey}.{$index}.quantity"] = "Quantity must be at least 1.";
            }
            if (empty(trim($item['condition'] ?? ''))) {
                $errors["{$itemsKey}.{$index}.condition"] = "Enter condition for \"{$item['item_name']}\".";
            }
        }

        return $errors;
    }

    /**
     * Upsert checklist and items to DB (preserves tenant_confirmed flags).
     */
    protected function upsertInspection(
        int $leaseId,
        string $modelClass,
        array $checklist,
        array $items,
        string $itemType
    ): void {
        foreach ($checklist as $item) {
            if (!empty($item['condition'])) {
                $modelClass::updateOrCreate(
                    [
                        'lease_id'  => $leaseId,
                        'type'      => 'checklist',
                        'item_name' => $item['item_name'],
                    ],
                    [
                        'condition'   => $item['condition'],
                        'remarks'     => $item['remarks'] ?? null,
                        'repair_cost' => !empty($item['repair_cost']) ? $item['repair_cost'] : null,
                    ]
                );
            }
        }

        foreach ($items as $item) {
            if (!empty($item['quantity']) || !empty($item['condition'])) {
                $modelClass::updateOrCreate(
                    [
                        'lease_id'  => $leaseId,
                        'type'      => $itemType,
                        'item_name' => $item['item_name'],
                    ],
                    [
                        'quantity'           => $item['quantity'] ?: null,
                        'quantity_returned'  => !empty($item['quantity_returned']) ? $item['quantity_returned'] : null,
                        'remarks'            => $item['condition'] ?? null,
                        'is_returned'        => $item['is_returned'] ?? false,
                        'replacement_cost'   => !empty($item['replacement_cost']) ? $item['replacement_cost'] : null,
                    ]
                );
            }
        }
    }

    /**
     * Handle the updated* hook for checklist condition fields.
     */
    protected function handleChecklistUpdate(string $key, string $checklistKey): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'condition') {
            $this->resetErrorBag("{$checklistKey}.{$parts[0]}.condition");
        }
    }

    /**
     * Handle the updated* hook for items quantity/condition fields.
     */
    protected function handleItemsUpdate($value, string $key, string $itemsKey, array &$items): void
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'quantity') {
            $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
            $items[$index]['quantity'] = $cleaned;

            $this->resetErrorBag("{$itemsKey}.{$index}.quantity");
            if ($cleaned === '' || $cleaned === null) {
                $this->addError("{$itemsKey}.{$index}.quantity", 'Required');
            } elseif ((int) $cleaned < 1) {
                $this->addError("{$itemsKey}.{$index}.quantity", 'Min 1');
            }
        }

        if ($field === 'quantity_returned') {
            $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
            $items[$index]['quantity_returned'] = $cleaned;

            $this->resetErrorBag("{$itemsKey}.{$index}.quantity_returned");
            $issuedQty = (int) ($items[$index]['quantity'] ?? 0);
            if ($cleaned !== '' && $issuedQty > 0 && (int) $cleaned > $issuedQty) {
                $this->addError("{$itemsKey}.{$index}.quantity_returned", "Cannot exceed issued qty ({$issuedQty})");
            }
        }

        if ($field === 'condition') {
            $this->resetErrorBag("{$itemsKey}.{$index}.condition");
            if (empty(trim((string) $value))) {
                $this->addError("{$itemsKey}.{$index}.condition", 'Required');
            }
        }

        // Flag any previous items that were skipped (no condition or quantity)
        $currentIndex = (int) $index;
        for ($i = 0; $i < $currentIndex; $i++) {
            if (empty(trim($items[$i]['condition'] ?? ''))) {
                $this->addError(
                    "{$itemsKey}.{$i}.condition",
                    "Please select a condition for \"{$items[$i]['item_name']}\"."
                );
            }
            if ($items[$i]['quantity'] === '' || $items[$i]['quantity'] === null) {
                $this->addError(
                    "{$itemsKey}.{$i}.quantity",
                    "Enter quantity for \"{$items[$i]['item_name']}\"."
                );
            }
        }
    }
}
