<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $brgy = Barangay::query()->with('city.province')->inRandomOrder()->first();
        $street = $this->faker->buildingNumber() . ' ' . $this->faker->streetName() . ' St.';

        return [
            'owner_id'          => $this->getLandlordId(),
            'building_name'     => $this->faker->company . ' Apartments',
            'province_id'       => $brgy?->city?->province_id,
            'city_id'           => $brgy?->city_id,
            'barangay_id'       => $brgy?->id,
            'street'            => $street,
            'address'           => $brgy
                ? "{$street}, {$brgy->name}, {$brgy->city->name}, {$brgy->city->province->name}"
                : $this->faker->address,
            'prop_description'  => $this->faker->optional()->paragraph(3),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }

    private function getLandlordId(): ?int
    {
        $landlord = User::where('role', 'landlord')->inRandomOrder()->first();

        // If no landlord exists, optionally create one
        if (!$landlord) {
            $landlord = User::factory()->create(['role' => 'landlord']);
        }

        return $landlord->user_id;
    }
}
