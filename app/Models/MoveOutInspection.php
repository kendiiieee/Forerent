<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoveOutInspection extends Model
{
    protected $fillable = [
        'lease_id',
        'type',
        'item_name',
        'condition',
        'quantity',
        'remarks',
        'repair_cost',
        'replacement_cost',
        'tenant_confirmed',
        'is_returned',
        'dispute_status',
        'dispute_remarks',
        'disputed_at',
        'resolution_remarks',
        'resolved_at',
    ];

    protected $casts = [
        'tenant_confirmed' => 'boolean',
        'is_returned' => 'boolean',
        'repair_cost' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'disputed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }
}
