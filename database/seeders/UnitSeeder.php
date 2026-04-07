<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    private const MAX_UNITS_PER_MANAGER = 10;
    private const FLOORS_PER_PROPERTY   = 5;
    private const UNITS_PER_FLOOR       = 4;

    public function run(): void
    {
        $properties = Property::all();
        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Run PropertySeeder first.');
            return;
        }

        $managers = User::where('role', 'manager')->pluck('user_id')->toArray();
        $managerUnitCounts = array_fill_keys($managers, 0);

        foreach ($properties as $property) {
            for ($floor = 1; $floor <= self::FLOORS_PER_PROPERTY; $floor++) {
                for ($unit = 1; $unit <= self::UNITS_PER_FLOOR; $unit++) {

                    $unitNumber = str_pad($floor, 2, '0', STR_PAD_LEFT)
                        . str_pad($unit, 2, '0', STR_PAD_LEFT);

                    $managerId = null;
                    if (!empty($managers)) {
                        $eligible = array_keys(array_filter(
                            $managerUnitCounts,
                            fn($count) => $count < self::MAX_UNITS_PER_MANAGER
                        ));

                        if (!empty($eligible)) {
                            $managerId = $eligible[array_rand($eligible)];
                            $managerUnitCounts[$managerId]++;
                        }
                    }

                    Unit::factory()->create([
                        'property_id' => $property->property_id,
                        'manager_id'  => $managerId,
                        'floor_number'=> $floor,
                        'unit_number' => $unitNumber,
                    ]);
                }
            }
        }

        $this->command->info('✅ Units seeded successfully using factory!');
    }
}
