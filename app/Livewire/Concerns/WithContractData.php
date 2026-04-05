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
        $billing  = $lease?->billings->first();

        return [
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
                'move_out_initiated_at'  => $lease?->move_out_initiated_at?->format('Y-m-d H:i'),
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
            'contract_status' => $lease?->contract_status ?? 'draft',
            'contract_settings' => $property?->contract_settings ?? [],
            'deposit_refund' => [
                'amount' => $lease?->deposit_refund_amount,
                'deductions' => $lease?->deposit_deductions,
            ],
            'outstanding_balances' => $this->buildOutstandingBalances($lease),
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
        $this->tenantSignedAt = $lease?->tenant_signed_at?->format('M d, Y h:i A');
        $this->ownerSignedAt = $lease?->owner_signed_at?->format('M d, Y h:i A');
        $this->contractAgreed = (bool) $lease?->contract_agreed;

        // Move-out
        $this->moveOutTenantSignature = $lease?->moveout_tenant_signature;
        $this->moveOutOwnerSignature = $lease?->moveout_owner_signature;
        $this->moveOutTenantSignedAt = $lease?->moveout_tenant_signed_at?->format('M d, Y h:i A');
        $this->moveOutOwnerSignedAt = $lease?->moveout_owner_signed_at?->format('M d, Y h:i A');
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

        $unpaidRent = 0;
        $unpaidElectricity = 0;
        $unpaidWater = 0;
        $lateFees = 0;
        $violationFines = 0;
        $otherCharges = 0;

        foreach ($unpaidBillings as $billing) {
            foreach ($billing->items as $item) {
                match ($item->charge_type) {
                    'advance', 'rent' => $unpaidRent += (float) $item->amount,
                    'electricity' => $unpaidElectricity += (float) $item->amount,
                    'water' => $unpaidWater += (float) $item->amount,
                    'late_fee' => $lateFees += (float) $item->amount,
                    'violation_fee' => $violationFines += (float) $item->amount,
                    default => $otherCharges += (float) $item->amount,
                };
            }
        }

        if ($unpaidRent > 0) {
            $balances[] = ['charge' => 'Unpaid Monthly Rent', 'period' => '', 'amount' => $unpaidRent];
        }
        if ($unpaidElectricity > 0) {
            $balances[] = ['charge' => 'Unpaid Electricity Share', 'period' => '', 'amount' => $unpaidElectricity];
        }
        if ($unpaidWater > 0) {
            $balances[] = ['charge' => 'Unpaid Water Share', 'period' => '', 'amount' => $unpaidWater];
        }
        if ($lateFees > 0) {
            $balances[] = ['charge' => 'Late Payment Fees', 'period' => '', 'amount' => $lateFees];
        }
        if ($violationFines > 0) {
            $balances[] = ['charge' => 'Violation Fines', 'period' => '', 'amount' => $violationFines];
        }
        if ($otherCharges > 0) {
            $balances[] = ['charge' => 'Other Charges', 'period' => '', 'amount' => $otherCharges];
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
     * Notify the tenant that the manager/owner signed a contract.
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
     * Notify the manager that the tenant signed a contract.
     */
    protected function notifyManagerOfSign(Lease $lease, string $contractType): void
    {
        $managerId = $this->findManagerIdForLease($lease);
        if (!$managerId) return;

        $user = \Illuminate\Support\Facades\Auth::user();
        $label = $contractType === 'move-out' ? 'move-out contract' : 'contract';

        Notification::create([
            'user_id' => $managerId,
            'type' => 'contract_signed',
            'title' => 'Contract Signed by Tenant',
            'message' => $user->first_name . ' ' . $user->last_name . ' has read and signed the ' . $label . '.',
            'link' => '/manager/tenant',
        ]);
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
                'item_name'   => $item,
                'condition'   => $saved?->condition ?? '',
                'remarks'     => $saved?->remarks ?? '',
                'repair_cost' => $saved?->repair_cost ?? '',
            ];
        }
        $this->$checklistProp = $checklist;

        $items = [];
        foreach ($itemsList as $item) {
            $saved = $savedItems->firstWhere('item_name', $item);
            $items[] = [
                'item_name'         => $item,
                'quantity'          => $saved?->quantity ?? '',
                'condition'         => $saved?->remarks ?? '',
                'tenant_confirmed'  => $saved?->tenant_confirmed ?? false,
                'is_returned'       => $saved?->is_returned ?? false,
                'replacement_cost'  => $saved?->replacement_cost ?? '',
            ];
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
                        'quantity'         => $item['quantity'] ?: null,
                        'remarks'          => $item['condition'] ?? null,
                        'is_returned'      => $item['is_returned'] ?? false,
                        'replacement_cost' => !empty($item['replacement_cost']) ? $item['replacement_cost'] : null,
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
