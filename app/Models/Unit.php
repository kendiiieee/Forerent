<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'property_id',
        'manager_id',
        'floor_number',
        'unit_number',
        'occupants',
        'bed_type',
        'room_type',
        'room_cap',
        'unit_cap',
        'price',
        'amenities'
    ];



    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'user_id');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class, 'unit_id', 'unit_id');
    }

    public function utilityBills()
    {
        return $this->hasMany(UtilityBill::class, 'unit_id', 'unit_id');
    }
}
