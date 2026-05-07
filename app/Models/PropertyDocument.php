<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyDocument extends Model
{
    protected $fillable = [
        'property_id',
        'file_path',
        'original_name',
        'category',
        'visibility',
        'is_seed',
    ];

    protected $casts = [
        'is_seed' => 'boolean',
    ];

    /**
     * Categories restricted to owner and manager only.
     */
    public const OWNER_MANAGER_CATEGORIES = [
        'business_permit',
        'bir_2303',
        'inspection_report',
        'barangay_clearance',
        'title_tct',
        'tax_declaration',
        'transfer_certificate',
    ];

    /**
     * Categories visible to tenants as well.
     */
    public const TENANT_VISIBLE_CATEGORIES = [
        'occupancy_permit',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
