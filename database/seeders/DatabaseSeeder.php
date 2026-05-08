<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Lease;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    protected Generator $faker;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->faker = app(Generator::class);

        Schema::disableForeignKeyConstraints();
        User::truncate();
        Property::truncate();
        Schema::enableForeignKeyConstraints();

        $this->call([
            PsgcSeeder::class,
            UserSeeder::class,
            PropertySeeder::class,
            PropertyDocumentSeeder::class,
            UnitSeeder::class,
            LeaseSeeder::class,
            UtilityBillSeeder::class,
            BillingSeeder::class,
            TransactionSeeder::class,
            MaintenanceSeeder::class,
            AnnouncementSeeder::class,
            PaymentRequestSeeder::class,
        ]);

        // Ensure Marcus Manager manages the unit where Tricia Tenant lives
        // and reset her active lease to draft so the move-in contract can be tested
        $marcus = User::where('first_name', 'Marcus')->where('role', 'manager')->first();
        $tricia = User::where('first_name', 'Tricia')->where('role', 'tenant')->first();
        if ($marcus && $tricia) {
            $lease = Lease::where('tenant_id', $tricia->user_id)->where('status', 'Active')->first();
            if ($lease) {
                $bed = Bed::find($lease->bed_id);
                if ($bed) {
                    Unit::where('unit_id', $bed->unit_id)->update(['manager_id' => $marcus->user_id]);
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
        $mia = User::where('first_name', 'Mia')->where('role', 'manager')->first();
        $tanya = User::where('first_name', 'Tanya')->where('role', 'tenant')->first();
        if ($mia && $tanya) {
            $lease = Lease::where('tenant_id', $tanya->user_id)->where('status', 'Active')->first();
            if ($lease) {
                $bed = Bed::find($lease->bed_id);
                if ($bed) {
                    Unit::where('unit_id', $bed->unit_id)->update(['manager_id' => $mia->user_id]);
                }
            }
        }
    }
}