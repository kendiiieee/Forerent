<?php

namespace App\Models\Concerns;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;

/**
 * Adds PSGC address relationships and keeps a legacy text column in sync.
 *
 * Implementing models declare a static array `$psgcAddressMap` describing each
 * address group on the model:
 *
 *   protected static array $psgcAddressMap = [
 *       // group key => [text column, prefix for FK columns]
 *       'permanent' => ['permanent_address', 'permanent_'],
 *   ];
 *
 * The trait wires up `{prefix}province`, `{prefix}city`, `{prefix}barangay`
 * relationships and a `saving` event that recomputes the text column from the
 * FK selections + street so existing reads keep working.
 */
trait HasPsgcAddress
{
    public static function bootHasPsgcAddress(): void
    {
        static::saving(function ($model) {
            foreach (static::$psgcAddressMap as $group => [$textColumn, $prefix]) {
                $provinceId = $model->{$prefix . 'province_id'} ?? null;
                $cityId     = $model->{$prefix . 'city_id'} ?? null;
                $barangayId = $model->{$prefix . 'barangay_id'} ?? null;
                $street     = trim((string) ($model->{$prefix . 'street'} ?? ''));

                if (!$provinceId && !$cityId && !$barangayId && $street === '') {
                    continue;
                }

                $parts = array_filter([
                    $street !== '' ? $street : null,
                    $barangayId ? optional(Barangay::find($barangayId))->name : null,
                    $cityId ? optional(City::find($cityId))->name : null,
                    $provinceId ? optional(Province::find($provinceId))->name : null,
                ]);

                if (count($parts) > 0) {
                    $model->{$textColumn} = implode(', ', $parts);
                }
            }
        });
    }

    public function permanentProvince() { return $this->belongsTo(Province::class, 'permanent_province_id'); }
    public function permanentCity()     { return $this->belongsTo(City::class, 'permanent_city_id'); }
    public function permanentBarangay() { return $this->belongsTo(Barangay::class, 'permanent_barangay_id'); }

    public function province() { return $this->belongsTo(Province::class, 'province_id'); }
    public function city()     { return $this->belongsTo(City::class, 'city_id'); }
    public function barangay() { return $this->belongsTo(Barangay::class, 'barangay_id'); }

    public function forwardingProvince() { return $this->belongsTo(Province::class, 'forwarding_province_id'); }
    public function forwardingCity()     { return $this->belongsTo(City::class, 'forwarding_city_id'); }
    public function forwardingBarangay() { return $this->belongsTo(Barangay::class, 'forwarding_barangay_id'); }
}
