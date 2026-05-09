<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    const FLOORS_PER_PROPERTY = 5;

    const UNITS_PER_FLOOR = 4;

    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);
        $managers = User::where('role', 'manager')->pluck('user_id')->toArray();

        $properties = Property::all();
        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Run PropertySeeder first.');

            return;
        }

        if (empty($managers)) {
            throw new \RuntimeException('UnitSeeder requires at least one user with role=manager.');
        }

        $rrIndex = 0;
        $managerCount = count($managers);

        foreach ($properties as $property) {
            for ($floor = 1; $floor <= self::FLOORS_PER_PROPERTY; $floor++) {
                // Keep '$unit' for this outer floor-loop
                for ($unit = 1; $unit <= self::UNITS_PER_FLOOR; $unit++) {

                    $floorFormatted = str_pad($floor, 2, '0', STR_PAD_LEFT);

                    // FIX: Changed '$unit' to '$i' here to avoid collision
                    for ($i = 1; $i <= 4; $i++) {

                        $unitFormatted = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $unitNumber = $floorFormatted.$unitFormatted;

                        $managerId = $managers[$rrIndex % $managerCount];
                        $rrIndex++;

                        Unit::factory()->create([
                            'property_id' => $property->property_id,
                            'manager_id' => $managerId,
                            'floor_number' => $floor,
                            'unit_number' => $unitNumber,
                        ]);
                    }
                }
            }

            $this->command->info('✅ Units seeded successfully using factory!');
        }
    }
}