<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceFeedback extends Model
{
    protected $table = 'maintenance_feedback';

    protected $fillable = [
        'request_id', 'tenant_id', 'rating', 'experience_tag', 'comment'
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }
}
