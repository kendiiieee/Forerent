<?php

namespace Database\Seeders;

use App\Models\Property;
use Faker\Generator;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    protected Generator $faker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->faker = app(Generator::class);

        $liam = \App\Models\User::where('email', 'landlord@example.com')->first();

        // Create 3 properties owned by Liam Landlord
        Property::factory()
            ->count(3)
            ->create(['owner_id' => $liam->user_id]);
    }
}
