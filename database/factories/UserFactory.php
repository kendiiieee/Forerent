<?php

namespace Database\Factories;

use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker ?? app(Generator::class);

        $firstName = $faker->firstName();
        $lastName  = $faker->lastName();

        $email = strtolower($firstName . '.' . $lastName) . '.' . $faker->unique()->numerify('###') . '@example.com';

        $gender = fake()->randomElement(['Male', 'Female']);
        $idTypes = ['Passport', "Driver's License", 'UMID', 'National ID', 'Postal ID'];

        return [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'gender'     => $gender,

            'email' => $email,

            'role' => $faker->randomElement(['tenant', 'manager', 'landlord']),

            'contact' => '9' . $faker->numerify('#########'),

            'profile_img' => 'https://i.pravatar.cc/150?u=' . $email,

            'password' => Hash::make('password'),

            'permanent_address'              => fake()->address(),
            'government_id_type'             => fake()->randomElement($idTypes),
            'government_id_number'           => strtoupper(Str::random(3)) . '-' . fake()->numerify('########'),
            'company_school'                 => fake()->company(),
            'position_course'                => fake()->jobTitle(),
            'emergency_contact_name'         => fake()->name(),
            'emergency_contact_relationship' => fake()->randomElement(['Parent', 'Sibling', 'Spouse', 'Friend', 'Guardian']),
            'emergency_contact_number'       => '9' . $faker->numerify('#########'),

            'email_verified_at' => now(),
            'phone_verified_at' => null,

            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
