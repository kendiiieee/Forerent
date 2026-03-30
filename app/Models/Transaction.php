<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'billing_id',
        'name',
        'reference_number',
        'or_number',
        'transaction_type',
        'category',
        'payment_method',
        'transaction_date',
        'amount',
        'is_recurring'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean'
    ];

    /**
     * Relationship with billing
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'billing_id', 'billing_id');
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredits($query)
    {
        return $query->where('transaction_type', 'Credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebits($query)
    {
        return $query->where('transaction_type', 'Debit');
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Get net amount (positive for credits, negative for debits)
     */
    public function getNetAmountAttribute()
    {
        return $this->transaction_type === 'Credit' ? $this->amount : -$this->amount;
    }

    public static function syncPrimaryKeySequence(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('transactions', 'transaction_id'), COALESCE(MAX(transaction_id), 0) + 1, false) FROM transactions"
        );
    }
}
