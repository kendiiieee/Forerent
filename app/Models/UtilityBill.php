<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UtilityBill extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'utility_bill_id';

    protected $fillable = [
        'unit_id',
        'utility_type',
        'billing_period',
        'total_amount',
        'tenant_count',
        'per_tenant_amount',
        'entered_by',
    ];

    protected $casts = [
        'billing_period' => 'date',
        'total_amount' => 'decimal:2',
        'per_tenant_amount' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by', 'user_id');
    }
}
