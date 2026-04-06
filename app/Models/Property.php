<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'property_id';

    protected $fillable = [
        'owner_id',
        'building_name',
        'address',
        'prop_description',
        'contract_settings',
    ];

    protected $casts = [
        'contract_settings' => 'array',
    ];

    /**
     * Get a contract setting with a default fallback.
     * Settings keys: house_rules, inclusions, exclusions, policies, penalty_schedule
     */
    public function getContractSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->contract_settings, $key, $default);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'property_id', 'property_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'property_id', 'property_id');
    }

    public function managers()
    {
        return $this->hasManyThrough(
            User::class,
            Unit::class,
            'property_id',
            'user_id',
            'property_id',
            'manager_id'
        )->distinct();
    }

    public function documents()
    {
        return $this->hasMany(PropertyDocument::class, 'property_id', 'property_id');
    }

    public function photos()
    {
        return $this->documents()->where('category', 'property_photo');
    }

    /**
     * Get the thumbnail URL (first uploaded property photo).
     */
    public function getThumbnailAttribute(): ?string
    {
        $firstPhoto = $this->photos()->oldest()->first();

        return $firstPhoto ? Storage::url($firstPhoto->file_path) : null;
    }

    public function tenantsForManager($managerId)
    {
        return User::whereHas('leases.bed.unit', function ($query) use ($managerId) {
            $query->where('manager_id', $managerId)
                ->where('property_id', $this->property_id)->distinct();
        });
    }

}
