<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsgcSeeder extends Seeder
{
    public function run(): void
    {
        $dataset = require database_path('data/psgc_ncr.php');

        DB::transaction(function () use ($dataset) {
            foreach ($dataset as $provinceData) {
                $province = Province::updateOrCreate(
                    ['psgc_code' => $provinceData['psgc_code']],
                    ['name' => $provinceData['name']]
                );

                foreach ($provinceData['cities'] as $cityData) {
                    $city = City::updateOrCreate(
                        ['psgc_code' => $cityData['psgc_code']],
                        [
                            'name'        => $cityData['name'],
                            'province_id' => $province->id,
                        ]
                    );

                    if (empty($cityData['barangays'])) {
                        continue;
                    }

                    $rows = collect($cityData['barangays'])->map(fn ($brgy) => [
                        'psgc_code'  => $brgy['psgc_code'],
                        'name'       => $brgy['name'],
                        'city_id'    => $city->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all();

                    Barangay::upsert($rows, ['psgc_code'], ['name', 'city_id', 'updated_at']);
                }
            }
        });
    }
}
