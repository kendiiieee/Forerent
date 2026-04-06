<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Violation;
use App\Models\Billing;
use App\Models\BillingItem;
use Illuminate\Support\Facades\DB;

class ViolationEscalationService
{
    /**
     * Determine the penalty for the next violation on a lease.
     *
     * @return array{offense_number: int, penalty_type: string, fine_amount: float|null}
     */
    public static function determinePenalty(Lease $lease, string $severity): array
    {
        $existingCount = Violation::where('lease_id', $lease->lease_id)
            ->whereNull('deleted_at')
            ->count();

        $offenseNumber = $existingCount + 1;

        // Serious violations (illegal activity, property destruction) = immediate termination
        if ($severity === 'serious') {
            return [
                'offense_number' => $offenseNumber,
                'penalty_type' => 'lease_termination',
                'fine_amount' => null,
            ];
        }

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

        if (!$billing) return;

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
}
