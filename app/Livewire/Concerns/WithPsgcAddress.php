<?php

namespace App\Livewire\Concerns;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Cache;

/**
 * Provides PSGC dropdown data for Livewire components hosting an
 * <x-forms.address-picker>. The Livewire component is still responsible for
 * declaring its own province/city/barangay/street properties (with whatever
 * prefix matches the field, e.g. permanent_*, forwarding_*) and for adding
 * `updated*` hooks that reset the dependent fields when a parent changes.
 *
 * Lookups are cached for 1 hour. PSGC data only changes when the seeder runs.
 */
trait WithPsgcAddress
{
    public function psgcProvinces()
    {
        return Cache::remember('psgc_provinces', 3600, fn () =>
            Province::orderBy('name')->get(['id', 'name'])
        );
    }

    public function psgcCities($provinceId)
    {
        if (!$provinceId) {
            return collect();
        }

        return Cache::remember("psgc_cities_{$provinceId}", 3600, fn () =>
            City::where('province_id', $provinceId)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function psgcBarangays($cityId)
    {
        if (!$cityId) {
            return collect();
        }

        return Cache::remember("psgc_barangays_{$cityId}", 3600, fn () =>
            Barangay::where('city_id', $cityId)->orderBy('name')->get(['id', 'name'])
        );
    }
}
