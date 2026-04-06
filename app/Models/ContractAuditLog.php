<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ContractAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lease_id',
        'user_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Log a contract event.
     */
    public static function log(int $leaseId, string $action, array $extra = []): self
    {
        return static::create(array_merge([
            'lease_id' => $leaseId,
            'user_id' => Auth::id(),
            'action' => $action,
            'created_at' => now(),
        ], $extra));
    }
}
