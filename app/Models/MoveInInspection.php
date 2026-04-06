<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoveInInspection extends Model
{
    protected $fillable = [
        'lease_id',
        'type',
        'item_name',
        'condition',
        'quantity',
        'remarks',
        'tenant_confirmed',
        'dispute_status',
        'dispute_remarks',
        'disputed_at',
        'resolution_remarks',
        'resolved_at',
    ];

    protected $casts = [
        'tenant_confirmed' => 'boolean',
        'disputed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }
}
