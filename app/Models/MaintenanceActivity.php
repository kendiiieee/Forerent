<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceActivity extends Model
{
    protected $fillable = ['request_id', 'user_id', 'action', 'details'];

    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
