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
        'tenant_confirmed',
    ];

    protected $casts = [
        'tenant_confirmed' => 'boolean',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }
}
