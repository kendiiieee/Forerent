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
        'moveout_contract_agreed',
        'moveout_contract_status',
        'moveout_signed_contract_path',
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
        'contract_agreed' => 'boolean',
        'moveout_tenant_signed_at' => 'datetime',
        'moveout_owner_signed_at' => 'datetime',
        'moveout_contract_agreed' => 'boolean',
        'deposit_refund_amount' => 'decimal:2',
        'deposit_deductions' => 'array',
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
     * Calculate the deposit refund at move-out.
     *
     * @param \Carbon\Carbon|null $originalEndDate Pass the original end_date if it was
     *        overwritten before calling this (e.g. confirmMoveOut sets end_date=today first).
     * @return array{refund_amount: float, deductions: array, deposit: float, total_deductions: float}
     */
    public function calculateDepositRefund(?\Carbon\Carbon $originalEndDate = null): array
    {
        $deposit = (float) $this->security_deposit;
        $endDate = $originalEndDate ?? $this->end_date;
        $deductions = [];

        // 1. Unpaid bills
        $unpaidBills = $this->billings()
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->sum('to_pay');
        if ($unpaidBills > 0) {
            $deductions[] = ['label' => 'Unpaid Bills', 'amount' => $unpaidBills];
        }

        // 2. Late fees (from billing items)
        $lateFees = \App\Models\BillingItem::whereHas('billing', fn($q) => $q->where('lease_id', $this->lease_id))
            ->where('charge_type', 'late_fee')
            ->sum('amount');
        if ($lateFees > 0) {
            $deductions[] = ['label' => 'Late Payment Fees', 'amount' => $lateFees];
        }

        // 3. Damage costs — use actual repair_cost entered by manager
        $moveInItems = $this->moveInInspections()->where('type', 'checklist')->get()->keyBy('item_name');
        $moveOutItems = $this->moveOutInspections()->where('type', 'checklist')->get()->keyBy('item_name');
        $damagedItems = [];
        $damageCost = 0;

        foreach ($moveOutItems as $name => $outItem) {
            $inItem = $moveInItems->get($name);
            if ($inItem && $inItem->condition !== $outItem->condition && $outItem->condition !== 'good') {
                $damagedItems[] = $name;
                $damageCost += (float) ($outItem->repair_cost ?? 0);
            }
        }
        if (!empty($damagedItems)) {
            $deductions[] = ['label' => 'Damage Repair Costs', 'amount' => $damageCost, 'items' => $damagedItems];
        }

        // 4. Unreturned items — use is_returned flag + replacement_cost
        $unreturnedInspections = $this->moveOutInspections()
            ->where('type', 'item_returned')
            ->where('is_returned', false)
            ->get();
        $unreturnedItems = $unreturnedInspections->pluck('item_name')->toArray();
        $replacementCost = (float) $unreturnedInspections->sum('replacement_cost');
        if (!empty($unreturnedItems)) {
            $deductions[] = ['label' => 'Unreturned Items', 'amount' => $replacementCost, 'items' => $unreturnedItems];
        }

        // 5. Early termination (if moved out before original end_date)
        if ($this->move_out && $endDate && $this->move_out->lt($endDate)) {
            $earlyFee = (float) ($this->early_termination_fee ?? $deposit);
            $deductions[] = ['label' => 'Early Termination Fee', 'amount' => $earlyFee];
        }

        $totalDeductions = collect($deductions)->sum('amount');
        $refund = max(0, $deposit - $totalDeductions);

        return [
            'refund_amount' => round($refund, 2),
            'deductions' => $deductions,
            'deposit' => $deposit,
            'total_deductions' => round($totalDeductions, 2),
        ];
    }
}
