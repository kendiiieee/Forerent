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
            PropertyDocumentSeeder::class,
            UnitSeeder::class,
            LeaseSeeder::class,
            UtilityBillSeeder::class,
            BillingSeeder::class,
            TransactionSeeder::class,
            MaintenanceSeeder::class,
            TransactionSeeder::class,
            AnnouncementSeeder::class,
            PaymentRequestSeeder::class,
        ]);

        // Ensure Marcus Manager manages the unit where Tricia Tenant lives
        // and reset her active lease to draft so the move-in contract can be tested
        $marcus = \App\Models\User::where('first_name', 'Marcus')->where('role', 'manager')->first();
        $tricia = \App\Models\User::where('first_name', 'Tricia')->where('role', 'tenant')->first();
        if ($marcus && $tricia) {
            $lease = \App\Models\Lease::where('tenant_id', $tricia->user_id)->where('status', 'Active')->first();
            if ($lease) {
                $bed = \App\Models\Bed::find($lease->bed_id);
                if ($bed) {
                    \App\Models\Unit::where('unit_id', $bed->unit_id)->update(['manager_id' => $marcus->user_id]);
                }

                // Reset contract to draft for testing the move-in contract flow
                $lease->update([
                    'contract_status' => 'draft',
                    'contract_agreed' => false,
                    'tenant_signature' => null,
                    'tenant_signed_at' => null,
                    'tenant_signed_ip' => null,
                    'owner_signature' => null,
                    'owner_signed_at' => null,
                    'owner_signed_ip' => null,
                    'manager_signature' => null,
                    'manager_signed_at' => null,
                    'manager_signed_ip' => null,
                    'signed_contract_path' => null,
                ]);

                // Clear any seeded move-in inspections so the flow starts fresh
                $lease->moveInInspections()->delete();
                $lease->auditLogs()->delete();
            }
        }

        // Ensure Mia Martinez manages the unit where Tanya Torres lives
        $mia = \App\Models\User::where('first_name', 'Mia')->where('role', 'manager')->first();
        $tanya = \App\Models\User::where('first_name', 'Tanya')->where('role', 'tenant')->first();
        if ($mia && $tanya) {
            $lease = \App\Models\Lease::where('tenant_id', $tanya->user_id)->where('status', 'Active')->first();
            if ($lease) {
                $bed = \App\Models\Bed::find($lease->bed_id);
                if ($bed) {
                    \App\Models\Unit::where('unit_id', $bed->unit_id)->update(['manager_id' => $mia->user_id]);
                }
            }
        }
    }
}
