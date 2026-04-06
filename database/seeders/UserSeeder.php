<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        User::factory()->create([
            'first_name' => 'Tricia',
            'last_name' => 'Tenant',
            'email' => 'tenant@example.com',
            'role' => 'tenant',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'first_name' => 'Tanya',
            'last_name' => 'Torres',
            'email' => 'tenant2@example.com',
            'role' => 'tenant',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'first_name' => 'Marcus',
            'last_name' => 'Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'first_name' => 'Mia',
            'last_name' => 'Martinez',
            'email' => 'manager2@example.com',
            'role' => 'manager',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'first_name' => 'Liam',
            'last_name' => 'Landlord',
            'email' => 'landlord@example.com',
            'role' => 'landlord',
            'password' => Hash::make('password'),
            'company_school' => 'ABC COMPANY',
        ]);


        User::factory()
            ->count(148)
            ->create([
                'role' => 'tenant',
                'password' => Hash::make('password'),
            ]);

        User::factory()
            ->count(3)
            ->create([
                'role' => 'manager',
                'password' => Hash::make('password'),
            ]);
    }
}
