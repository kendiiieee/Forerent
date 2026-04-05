<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceLog extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'request_id', 'completion_date', 'cost', 'description', 'charged_to'
    ];

    protected $casts = [
        'completion_date' => 'date',
    ];

    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id');
    }
}
