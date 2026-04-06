<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'violation_id';

    protected $fillable = [
        'lease_id', 'reported_by', 'violation_number', 'offense_number',
        'category', 'description', 'evidence_path', 'severity', 'penalty_type',
        'fine_amount', 'status', 'violation_date', 'issued_at',
        'tenant_acknowledged_at', 'resolution_notes', 'resolved_at',
        'billing_item_id',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'issued_at' => 'datetime',
        'tenant_acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    public function getEvidencePathsAttribute(): array
    {
        if (empty($this->evidence_path)) return [];
        $decoded = json_decode($this->evidence_path, true);
        return is_array($decoded) ? $decoded : [$this->evidence_path];
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by', 'user_id');
    }

    public function billingItem()
    {
        return $this->belongsTo(BillingItem::class, 'billing_item_id', 'billing_item_id');
    }
}
