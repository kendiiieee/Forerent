<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    protected Generator $faker;

    private const MAX_UNITS_PER_MANAGER = 10;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $properties = Property::all();
        $managers = User::where('role', 'manager')->pluck('user_id')->toArray();

        // Track how many units each manager has been assigned
        $managerUnitCounts = array_fill_keys($managers, 0);

        foreach ($properties as $property) {

            for ($floor = 1; $floor <= 5; $floor++) {

                $floorFormatted = str_pad($floor, 2, '0', STR_PAD_LEFT); // "01", "02", ...

                for ($unit = 1; $unit <= 4; $unit++) {

                    $unitFormatted = str_pad($unit, 2, '0', STR_PAD_LEFT); // "01", "02", "03", "04"

                    $unitNumber = $floorFormatted . $unitFormatted; // e.g., "0101"

                    // 30% chance of having no manager
                    $managerId = null;
                    if (mt_rand(1, 100) > 30 && !empty($managers)) {
                        // Only pick from managers who haven't hit the 10-unit cap
                        $eligible = array_filter($managers, fn($id) => $managerUnitCounts[$id] < self::MAX_UNITS_PER_MANAGER);

                        if (!empty($eligible)) {
                            $managerId = $eligible[array_rand($eligible)];
                            $managerUnitCounts[$managerId]++;
                        }
                    }

                    Unit::factory()
                        ->create([
                            'property_id'  => $property->property_id,
                            'manager_id'   => $managerId,
                            'floor_number' => $floor,
                            'unit_number'  => $unitNumber,
                        ]);
                }
            }
        }
    }
}
