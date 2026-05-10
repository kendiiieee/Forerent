<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Violation;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\ContractAuditLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class ViolationEscalationService
{
    /**
     * Determine the penalty for the next violation on a lease.
     *
     * Penalty escalates purely by offense count, regardless of severity.
     * Severity is recorded for context but does not influence the schedule.
     *
     * @return array{offense_number: int, penalty_type: string, fine_amount: float|null}
     */
    public static function determinePenalty(Lease $lease): array
    {
        $existingCount = Violation::where('lease_id', $lease->lease_id)
            ->whereNull('deleted_at')
            ->count();

        $offenseNumber = $existingCount + 1;

        // Get fine amount from property contract_settings
        $fineAmount = 500.00; // default
        $property = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->where('leases.lease_id', $lease->lease_id)
            ->select('properties.contract_settings')
            ->first();

        if ($property && $property->contract_settings) {
            $settings = json_decode($property->contract_settings, true);
            $fineAmount = data_get($settings, 'violation_fine', 500.00);
        }

        // Follow penalty schedule:
        // 1st offense = written warning
        // 2nd offense = fine
        // 3rd+ offense = lease termination
        return match (true) {
            $offenseNumber === 1 => [
                'offense_number' => 1,
                'penalty_type' => 'written_warning',
                'fine_amount' => null,
            ],
            $offenseNumber === 2 => [
                'offense_number' => 2,
                'penalty_type' => 'fine',
                'fine_amount' => (float) $fineAmount,
            ],
            default => [
                'offense_number' => $offenseNumber,
                'penalty_type' => 'lease_termination',
                'fine_amount' => null,
            ],
        };
    }

    /**
     * Apply the penalty: create billing item for fines.
     */
    public static function applyPenalty(Violation $violation): void
    {
        if ($violation->penalty_type !== 'fine' || !$violation->fine_amount) {
            return;
        }

        // Find the tenant's latest active billing
        $billing = Billing::where('lease_id', $violation->lease_id)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->orderBy('billing_date', 'desc')
            ->first();

        // No active billing — create a standalone "Violation Charges" billing
        if (!$billing) {
            $lease = Lease::find($violation->lease_id);
            $dueDate = now()->addDays(5);
            $billing = Billing::create([
                'lease_id'     => $violation->lease_id,
                'tenant_id'    => $lease?->tenant_id,
                'billing_type' => 'charges',
                'billing_date' => now(),
                'next_billing' => $dueDate,
                'due_date'     => $dueDate,
                'to_pay'       => 0,
                'amount'       => 0,
                'status'       => 'Unpaid',
            ]);
        }

        $billingItem = BillingItem::create([
            'billing_id' => $billing->billing_id,
            'charge_category' => 'conditional',
            'charge_type' => 'violation_fee',
            'description' => "Violation ({$violation->violation_number}): {$violation->category} — {$violation->description}",
            'amount' => $violation->fine_amount,
        ]);

        // Update billing totals
        $billing->update([
            'amount' => $billing->amount + $violation->fine_amount,
            'to_pay' => $billing->to_pay + $violation->fine_amount,
        ]);

        // Link billing item to violation
        $violation->update(['billing_item_id' => $billingItem->billing_item_id]);
    }

    /**
     * Issue a formal Notice of Termination on the lease.
     *
     * Idempotent: if a notice has already been issued, the existing record is kept
     * (multiple termination-grade violations don't reset the vacate-by clock).
     *
     * The notice period defaults to 30 days but can be overridden per property
     * via contract_settings.termination_notice_period_days. Common range is 15–30
     * days for dorms; the vacate-by date is computed from issuance + notice period.
     *
     * @return Lease|null The lease with the notice fields hydrated, or null if not applicable.
     */
    public static function issueTerminationNotice(Violation $violation): ?Lease
    {
        if ($violation->penalty_type !== 'lease_termination') {
            return null;
        }

        $lease = Lease::with('bed.unit.property')->find($violation->lease_id);
        if (!$lease) return null;

        // Don't reissue if a notice is already on file for this lease
        if ($lease->termination_notice_issued_at) {
            return $lease;
        }

        $property = $lease->bed?->unit?->property;
        $noticePeriodDays = (int) ($property?->getContractSetting('termination_notice_period_days', 30) ?? 30);

        $issuedAt = now();
        $vacateBy = $issuedAt->copy()->addDays($noticePeriodDays)->toDateString();

        $lease->update([
            'termination_notice_issued_at'    => $issuedAt,
            'vacate_by_date'                  => $vacateBy,
            'termination_notice_violation_id' => $violation->violation_id,
        ]);

        ContractAuditLog::log($lease->lease_id, 'termination_notice_issued', [
            'metadata' => [
                'violation_id'        => $violation->violation_id,
                'violation_number'    => $violation->violation_number,
                'notice_period_days'  => $noticePeriodDays,
                'vacate_by_date'      => $vacateBy,
            ],
        ]);

        $lease->refresh();

        // Tenant: formal notice with vacate-by date.
        Notification::create([
            'user_id' => $lease->tenant_id,
            'type'    => 'termination_notice_issued',
            'title'   => 'Notice of Lease Termination',
            'message' => "A formal Notice of Termination has been issued ({$noticePeriodDays}-day notice period). You must vacate the premises by " . $lease->vacate_by_date->format('M d, Y') . " and coordinate with management for the move-out inspection and settlement.",
            'link'    => '/tenant',
        ]);

        // Landlord/owner: same termination event, framed for the property owner.
        $ownerId = $property?->owner_id;
        if ($ownerId) {
            $tenantName = trim(($lease->tenant?->first_name ?? '') . ' ' . ($lease->tenant?->last_name ?? ''));
            $unitNumber = $lease->bed?->unit?->unit_number ?? '—';
            Notification::create([
                'user_id' => $ownerId,
                'type'    => 'termination_notice_issued',
                'title'   => 'Termination Notice Issued — Unit ' . $unitNumber,
                'message' => "A formal Notice of Termination has been issued for {$tenantName} (Unit {$unitNumber}) following 3 documented violations. Vacate-by date: " . $lease->vacate_by_date->format('M d, Y') . ". Reference: {$violation->violation_number}.",
                'link'    => '/landlord/property',
            ]);
        }

        return $lease;
    }
}
