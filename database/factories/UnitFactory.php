<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    private array $amenityKeys = [
        'Free_Wifi',
        'Hot_Cold_Shower',
        'Electric_Fan',
        'Water_Kettle',
        'Closet_Cabinet',
        'Housekeeping',
        'Refrigerator',
        'Microwave',
        'Rice_Cooker',
        'Dining_Table',
        'Utility_Subsidy',
        'AC_Unit',
        'Induction_Cooker',
        'Washing_Machine',
        'Access_Pool',
        'Access_Gym',
    ];

    public function definition(): array
    {
        $bedType    = $this->faker->randomElement(['Single', 'Bunk']);
        $roomCap    = match ($bedType) {
            'Single' => 1,
            'Bunk'   => 4,
            default  => 1,
        };
        $livingArea  = round($this->faker->randomFloat(1, 80, 200), 1);
        $floorNumber = $this->faker->numberBetween(1, 5);

        // Randomly select amenities
        $selectedAmenities = array_values(array_filter(
            $this->amenityKeys,
            fn() => (bool) rand(0, 1)
        ));

        // Build boolean amenity map
        $amenityMap = array_fill_keys($this->amenityKeys, false);
        foreach ($selectedAmenities as $key) {
            $amenityMap[$key] = true;
        }

        // Derive furnishing from selected amenity count
        $furnishing = $this->calculateFurnishing($amenityMap);

        // Get predicted price from API
        $price = $this->predictPrice(
            livingArea: $livingArea,
            floorNumber: $floorNumber,
            bedType: $bedType,
            roomCap: $roomCap,
            furnishing: $furnishing,
            amenityMap: $amenityMap,
        );

        return [
            'property_id'  => Property::factory(),
            'manager_id'   => User::where('role', 'manager')->inRandomOrder()->value('user_id'),
            'floor_number' => $floorNumber,
            'unit_number'  => '0000', // overridden by seeder if needed
            'occupants'    => $this->faker->randomElement(['Male', 'Female', 'Co-ed']),
            'living_area'  => $livingArea,
            'furnishing'   => $furnishing,
            'bed_type'     => $bedType,
            'room_cap'     => $roomCap,
            'price'        => $price,
            'amenities'    => json_encode($selectedAmenities),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Unit $unit) {
            $bedCount = match ($unit->bed_type) {
                'Single' => 1,
                'Bunk'   => 4,
                default  => 1,
            };

            for ($i = 1; $i <= $bedCount; $i++) {
                Bed::factory()->create([
                    'unit_id'    => $unit->unit_id,
                    'bed_number' => 'B' . $i,
                ]);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Helpers (mirrors AddUnitModal logic)
    // -------------------------------------------------------------------------

    private function calculateFurnishing(array $amenityMap): string
    {
        $selected = count(array_filter($amenityMap));
        $total    = count($amenityMap);

        if ($selected === 0) {
            return 'Bare';
        } elseif ($selected === $total) {
            return 'Fully Furnished';
        } else {
            return 'Semi-furnished';
        }
    }

    private function predictPrice(
        float  $livingArea,
        int    $floorNumber,
        string $bedType,
        int    $roomCap,
        string $furnishing,
        array  $amenityMap,
    ): int {
        $payload = array_merge([
            'Living Area (sqft)' => $livingArea,
            'Floor'              => $floorNumber,
            'Bed type'           => $bedType,
            'Room capacity'      => $roomCap,
            'Furnishing'         => $furnishing,
        ], $amenityMap);

        try {
            $response = Http::timeout(5)->post('http://price_api:8000/predict', $payload);

            if ($response->successful()) {
                return (int) $response->json('predicted_price');
            }

            Log::warning('Price API returned non-200 during factory; using fallback.', [
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Price API unreachable during factory; using fallback.', [
                'error' => $e->getMessage(),
            ]);
        }

        return rand(5000, 15000);
    }
}
