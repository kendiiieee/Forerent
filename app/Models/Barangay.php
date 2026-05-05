<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = ['psgc_code', 'name', 'city_id'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
