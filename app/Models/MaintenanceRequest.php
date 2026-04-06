<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'lease_id', 'status', 'logged_by', 'ticket_number',
        'log_date', 'problem', 'urgency', 'category', 'image_path',
        'assigned_to', 'expected_completion_date'
    ];

    protected $casts = [
        'log_date' => 'date',
        'expected_completion_date' => 'date',
    ];

    /**
     * Decode image_path JSON into an array. Supports JSON array (new) and plain string (old).
     */
    public function getImagePathsAttribute(): array
    {
        if (empty($this->image_path)) return [];
        $decoded = json_decode($this->image_path, true);
        return is_array($decoded) ? $decoded : [$this->image_path];
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class, 'lease_id', 'lease_id');
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'request_id', 'request_id');
    }

    public function feedback()
    {
        return $this->hasMany(MaintenanceFeedback::class, 'request_id', 'request_id');
    }

    public function notes()
    {
        return $this->hasMany(MaintenanceNote::class, 'request_id', 'request_id');
    }

    public function activities()
    {
        return $this->hasMany(MaintenanceActivity::class, 'request_id', 'request_id');
    }
}
