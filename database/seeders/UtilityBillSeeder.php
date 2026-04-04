<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UtilityBill;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UtilityBillSeeder extends Seeder
{
    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $units = Unit::whereHas('beds.leases')
            ->with(['beds.leases'])
            ->get();

        if ($units->isEmpty()) {
            $this->command->error('No units with leases found. Run LeaseSeeder first.');
            return;
        }

        foreach ($units as $unit) {
            if (!$unit->manager_id) continue;

            // Collect all leases across all beds in this unit
            $allLeases = $unit->beds->flatMap->leases;

            if ($allLeases->isEmpty()) continue;

            // Dynamically determine billing range from lease dates
            $earliestStart = $allLeases->min(fn($l) => $l->start_date);
            $latestEnd     = $allLeases->max(fn($l) => $l->end_date);

            $currentMonth = Carbon::parse($earliestStart)->startOfMonth();
            $endMonth     = Carbon::parse($latestEnd)->startOfMonth()->min(Carbon::now()->startOfMonth());

            while ($currentMonth->lte($endMonth)) {
                // Count tenants with a lease covering this month
                $activeTenantCount = $allLeases->filter(function ($lease) use ($currentMonth) {
                    $leaseStart = Carbon::parse($lease->start_date)->startOfMonth();
                    $leaseEnd   = Carbon::parse($lease->end_date)->startOfMonth();

                    return $leaseStart->lte($currentMonth) && $leaseEnd->gte($currentMonth);
                })->count();

                if ($activeTenantCount === 0) {
                    $currentMonth->addMonth();
                    continue;
                }

                // Electricity (always)
                $electricityTotal     = $this->faker->randomFloat(2, 1200, 2500);
                $electricityPerTenant = round($electricityTotal / $activeTenantCount, 2);

                UtilityBill::create([
                    'unit_id'           => $unit->unit_id,
                    'utility_type'      => 'electricity',
                    'billing_period'    => $currentMonth->format('Y-m-d'),
                    'total_amount'      => $electricityTotal,
                    'tenant_count'      => $activeTenantCount,
                    'per_tenant_amount' => $electricityPerTenant,
                    'entered_by'        => $unit->manager_id,
                ]);

                // Water (~60% chance)
                if ($this->faker->boolean(60)) {
                    $waterTotal     = $this->faker->randomFloat(2, 200, 500);
                    $waterPerTenant = round($waterTotal / $activeTenantCount, 2);

                    UtilityBill::create([
                        'unit_id'           => $unit->unit_id,
                        'utility_type'      => 'water',
                        'billing_period'    => $currentMonth->format('Y-m-d'),
                        'total_amount'      => $waterTotal,
                        'tenant_count'      => $activeTenantCount,
                        'per_tenant_amount' => $waterPerTenant,
                        'entered_by'        => $unit->manager_id,
                    ]);
                }

                $currentMonth->addMonth();
            }
        }

        $this->command->info('✅ Utility bills seeded successfully!');
    }
}
