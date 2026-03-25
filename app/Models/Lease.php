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
        'tenant_id', 'bed_id', 'status', 'term', 'auto_renew',
        'start_date', 'end_date', 'contract_rate', 'advance_amount',
        'security_deposit', 'move_in',
        'shift',
        'move_out',
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
        'moveout_tenant_signature',
        'moveout_tenant_signed_at',
        'moveout_tenant_signed_ip',
        'moveout_owner_signature',
        'moveout_owner_signed_at',
        'moveout_owner_signed_ip',
        'moveout_contract_agreed',
        'moveout_signed_contract_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'move_in' => 'date',
        'move_out' => 'date',
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
}
