<?php

namespace Database\Factories;

use App\Models\Barangay;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        // Random NCR address from seeded PSGC data — falls back to free-text
        // if PsgcSeeder hasn't run yet (so factory-only tests still work).
        $brgy = Barangay::query()->with('city.province')->inRandomOrder()->first();
        $street = $faker->buildingNumber() . ' ' . $faker->streetName() . ' St.';

        return [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'gender'     => $gender,

            'email' => $email,

            'role' => $faker->randomElement(['tenant', 'manager', 'landlord']),

            'contact' => '9' . $faker->numerify('#########'),

            'profile_img' => 'https://i.pravatar.cc/150?u=' . $email,

            'password' => Hash::make('password'),

            'permanent_province_id' => $brgy?->city?->province_id,
            'permanent_city_id'     => $brgy?->city_id,
            'permanent_barangay_id' => $brgy?->id,
            'permanent_street'      => $street,
            'permanent_address'              => $brgy
                ? "{$street}, {$brgy->name}, {$brgy->city->name}, {$brgy->city->province->name}"
                : fake()->address(),
            'government_id_type'             => fake()->randomElement($idTypes),
            'government_id_number'           => strtoupper(Str::random(3)) . '-' . fake()->numerify('########'),
            'government_id_image'            => $this->generatePlaceholderId($firstName),
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

    private function generatePlaceholderId(string $name): string
    {
        $path = 'government-ids/sample-id-' . strtolower($name) . '.jpg';

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory('government-ids');

            $img = imagecreatetruecolor(400, 250);
            $bg = imagecolorallocate($img, 240, 240, 240);
            $textColor = imagecolorallocate($img, 80, 80, 80);
            imagefilledrectangle($img, 0, 0, 399, 249, $bg);
            imagestring($img, 5, 120, 100, 'Government ID', $textColor);
            imagestring($img, 4, 140, 130, ucfirst($name), $textColor);

            ob_start();
            imagejpeg($img, null, 90);
            $imageData = ob_get_clean();
            imagedestroy($img);

            Storage::disk('public')->put($path, $imageData);
        }

        return $path;
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
