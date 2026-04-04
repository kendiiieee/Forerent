<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Generator;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    protected Generator $faker;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->faker = app(Generator::class);


        $this->call([
            UserSeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
            LeaseSeeder::class,
            BillingSeeder::class,
            UtilityBillSeeder::class,
            TransactionSeeder::class,
            MaintenanceSeeder::class,
            TransactionSeeder::class,
            AnnouncementSeeder::class,
        ]);

        // Ensure Marcus Manager manages the unit where Tricia Tenant lives
        $marcus = \App\Models\User::where('first_name', 'Marcus')->where('role', 'manager')->first();
        $tricia = \App\Models\User::where('first_name', 'Tricia')->where('role', 'tenant')->first();
        if ($marcus && $tricia) {
            $lease = \App\Models\Lease::where('tenant_id', $tricia->user_id)->where('status', 'Active')->first();
            if ($lease) {
                $bed = \App\Models\Bed::find($lease->bed_id);
                if ($bed) {
                    \App\Models\Unit::where('unit_id', $bed->unit_id)->update(['manager_id' => $marcus->user_id]);
                }
            }
        }
    }
}
