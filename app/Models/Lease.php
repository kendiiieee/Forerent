<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'lease_id';

    protected $fillable = [
        'tenant_id', 'bed_id', 'status', 'contract_status', 'term', 'auto_renew',
        'start_date', 'end_date', 'contract_rate', 'advance_amount',
        'security_deposit', 'move_in',
        'shift',
        'move_out',
        'move_out_initiated_at',
        'monthly_due_date',
        'late_payment_penalty',
        'short_term_premium',
        'reservation_fee_paid',
        'early_termination_fee',
        'tenant_signature',
        'tenant_signed_at',
        'tenant_signed_ip',
        'owner_signature',
        'owner_signed_at',
        'owner_signed_ip',
        'manager_signature',
        'manager_signed_at',
        'manager_signed_ip',
        'signed_contract_path',
        'contract_agreed',
        'forwarding_address',
        'reason_for_vacating',
        'deposit_refund_method',
        'deposit_refund_account',
        'deposit_refund_amount',
        'deposit_deductions',
        'moveout_tenant_signature',
        'moveout_tenant_signed_at',
        'moveout_tenant_signed_ip',
        'moveout_owner_signature',
        'moveout_owner_signed_at',
        'moveout_owner_signed_ip',
        'moveout_manager_signature',
        'moveout_manager_signed_at',
        'moveout_manager_signed_ip',
        'moveout_contract_agreed',
        'moveout_contract_status',
        'moveout_signed_contract_path',
        'deposit_interest_amount',
        'deposit_refund_deadline',
        'deposit_refund_completed_at',
        'deposit_refund_reference',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'move_in' => 'date',
        'move_out' => 'date',
        'move_out_initiated_at' => 'datetime',
        'auto_renew' => 'boolean',
        'contract_rate' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'late_payment_penalty' => 'decimal:2',
        'short_term_premium' => 'decimal:2',
        'reservation_fee_paid' => 'decimal:2',
        'early_termination_fee' => 'decimal:2',
        'monthly_due_date' => 'integer',
        'tenant_signed_at' => 'datetime',
        'owner_signed_at' => 'datetime',
        'manager_signed_at' => 'datetime',
        'contract_agreed' => 'boolean',
        'moveout_tenant_signed_at' => 'datetime',
        'moveout_owner_signed_at' => 'datetime',
        'moveout_manager_signed_at' => 'datetime',
        'moveout_contract_agreed' => 'boolean',
        'deposit_refund_amount' => 'decimal:2',
        'deposit_deductions' => 'array',
        'deposit_interest_amount' => 'decimal:2',
        'deposit_refund_deadline' => 'date',
        'deposit_refund_completed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id', 'bed_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'lease_id', 'lease_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'lease_id', 'lease_id');
    }

    public function moveInInspections()
    {
        return $this->hasMany(MoveInInspection::class, 'lease_id', 'lease_id');
    }

    public function moveOutInspections()
    {
        return $this->hasMany(MoveOutInspection::class, 'lease_id', 'lease_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(ContractAuditLog::class, 'lease_id', 'lease_id');
    }

    public function violations()
    {
        return $this->hasMany(Violation::class, 'lease_id', 'lease_id');
    }

    /**
     * Auto-compute deposit interest based on the property's configured savings rate.
     * RA 9653 IRR Section 7b requires interest on security deposits.
     *
     * The rate is configurable per property via contract_settings.deposit_interest_rate
     * (annual %, e.g. 1.25 = 1.25% per year). This allows each owner to set their
     * depository bank's actual savings rate — transparent and defensible.
     *
     * @return float computed interest amount
     */
    public function computeDepositInterest(): float
    {
        $deposit = (float) $this->security_deposit;
        if ($deposit <= 0) return 0;

        $startDate = $this->move_in ?? $this->start_date;
        $endDate = $this->move_out ?? now();
        if (!$startDate) return 0;

        // Pull rate from property contract_settings, default to 0 if not configured
        $property = $this->bed?->unit?->property;
        $annualRatePercent = (float) ($property?->getContractSetting('deposit_interest_rate', 0) ?? 0);
        if ($annualRatePercent <= 0) return 0;

        $annualRate = $annualRatePercent / 100; // Convert from % to decimal
        $daysHeld = $startDate->diffInDays($endDate);
        $interest = $deposit * ($annualRate / 365) * $daysHeld;

        return round($interest, 2);
    }

    /**
     * Calculate the deposit refund at move-out.
     *
     * Uses only UNPAID billing amounts for deductions (not all billing items)
     * to prevent double-counting charges that the tenant already paid.
     *
     * @param \Carbon\Carbon|null $originalEndDate Pass the original end_date if it was
     *        overwritten before calling this (e.g. confirmMoveOut sets end_date=today first).
     * @return array{refund_amount: float, deductions: array, deposit: float, total_deductions: float, interest_earned: float}
     */
    public function calculateDepositRefund(?\Carbon\Carbon $originalEndDate = null): array
    {
        $deposit = (float) $this->security_deposit;
        $endDate = $originalEndDate ?? $this->end_date;
        $deductions = [];

        // 1. Unpaid bills — only from UNPAID/OVERDUE billings to prevent double-counting
        //    with charges the tenant already paid directly
        $unpaidBillings = $this->billings()
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->with('items')
            ->get();

        $unpaidRent = 0;
        $lateFees = 0;
        $violationFines = 0;
        $otherUnpaid = 0;

        foreach ($unpaidBillings as $billing) {
            foreach ($billing->items as $item) {
                match ($item->charge_type) {
                    'advance', 'rent', 'electricity', 'water' => $unpaidRent += (float) $item->amount,
                    'late_fee' => $lateFees += (float) $item->amount,
                    'violation_fee' => $violationFines += (float) $item->amount,
                    default => $otherUnpaid += (float) $item->amount,
                };
            }
        }

        if ($unpaidRent > 0) {
            $deductions[] = ['label' => 'Unpaid Bills (Rent & Utilities)', 'amount' => $unpaidRent];
        }
        if ($lateFees > 0) {
            $deductions[] = ['label' => 'Late Payment Fees', 'amount' => $lateFees];
        }
        if ($violationFines > 0) {
            $deductions[] = ['label' => 'Violation Fines', 'amount' => $violationFines];
        }
        if ($otherUnpaid > 0) {
            $deductions[] = ['label' => 'Other Unpaid Charges', 'amount' => $otherUnpaid];
        }

        // 2. Advance rent credit — deduct from unpaid balance
        $advanceCredit = (float) ($this->advance_amount ?? 0);
        if ($advanceCredit > 0 && ($unpaidRent + $lateFees + $violationFines + $otherUnpaid) > 0) {
            $deductions[] = ['label' => 'Advance Rent Credit (applied)', 'amount' => -$advanceCredit];
        }

        // 3. Damage costs — improved detection: flag damage even without move-in record
        $moveInItems = $this->moveInInspections()->where('type', 'checklist')->get()->keyBy('item_name');
        $moveOutItems = $this->moveOutInspections()->where('type', 'checklist')->get()->keyBy('item_name');
        $damagedItems = [];
        $damageCost = 0;

        foreach ($moveOutItems as $name => $outItem) {
            $inItem = $moveInItems->get($name);
            $outCond = $outItem->condition;
            $inCond = $inItem?->condition;

            // Damage if: condition worsened, OR move-out is damaged/poor/missing even without move-in record
            $conditionWorsened = $inCond && $inCond !== $outCond && $outCond !== 'good';
            $noMoveInButDamaged = !$inCond && in_array($outCond, ['damaged', 'poor', 'missing']);

            if ($conditionWorsened || $noMoveInButDamaged) {
                $damagedItems[] = $name;
                $damageCost += (float) ($outItem->repair_cost ?? 0);
            }
        }
        if (!empty($damagedItems)) {
            $deductions[] = ['label' => 'Damage Repair Costs', 'amount' => $damageCost, 'items' => $damagedItems];
        }

        // 4. Unreturned / partially returned items — charge replacement for missing quantity
        $returnedInspections = $this->moveOutInspections()
            ->where('type', 'item_returned')
            ->get();
        $missingItems = [];
        $replacementCost = 0;

        foreach ($returnedInspections as $item) {
            $isReturned = (bool) $item->is_returned;
            $qtyIssued = (int) ($item->quantity ?? 0);
            $qtyReturned = (int) ($item->quantity_returned ?? 0);
            $isPartial = $isReturned && $qtyIssued > 0 && $qtyReturned < $qtyIssued;

            if (!$isReturned || $isPartial) {
                $missingItems[] = $item->item_name . ($isPartial ? " ({$qtyReturned}/{$qtyIssued} returned)" : '');
                $replacementCost += (float) ($item->replacement_cost ?? 0);
            }
        }
        if (!empty($missingItems)) {
            $deductions[] = ['label' => 'Unreturned / Missing Items', 'amount' => $replacementCost, 'items' => $missingItems];
        }

        // 5. Early termination — full deposit forfeiture (Contract Section 7, Civil Code Art. 1306)
        //    No additional fee beyond forfeiture. Tenant agreed to this at move-in signing.
        $isEarlyTermination = $this->move_out && $endDate && $this->move_out->lt($endDate);
        if ($isEarlyTermination) {
            $deductions[] = ['label' => 'Early Termination — Deposit Forfeiture', 'amount' => $deposit];
        }

        $totalDeductions = collect($deductions)->sum('amount');

        // Auto-compute deposit interest (RA 9653 IRR Section 7b)
        $interest = (float) ($this->deposit_interest_amount ?? $this->computeDepositInterest());

        $refund = $isEarlyTermination
            ? 0  // Full forfeiture — no refund regardless of interest
            : max(0, $deposit - $totalDeductions + $interest);

        return [
            'refund_amount' => round($refund, 2),
            'deductions' => $deductions,
            'deposit' => $deposit,
            'total_deductions' => round(max(0, $totalDeductions), 2),
            'interest_earned' => round($interest, 2),
        ];
    }
}
