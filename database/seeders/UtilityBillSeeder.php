<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\UtilityBill;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UtilityBillSeeder extends Seeder
{
    protected Generator $faker;

    private const CHUNK_SIZE = 500;

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
            $allLeases = $unit->beds->flatMap->leases;
            if ($allLeases->isEmpty()) continue;

            $earliestStart = $allLeases->min(fn($l) => $l->start_date);
            $latestEnd     = $allLeases->max(fn($l) => $l->end_date);

            $currentMonth = Carbon::parse($earliestStart)->startOfMonth();
            $endMonth     = Carbon::parse($latestEnd)->startOfMonth()->min(Carbon::now()->startOfMonth());

            while ($currentMonth->lte($endMonth)) {
                $activeLeases = $allLeases->filter(function ($lease) use ($currentMonth) {
                    $leaseStart = Carbon::parse($lease->start_date)->startOfMonth();
                    $leaseEnd   = Carbon::parse($lease->end_date)->startOfMonth();
                    return $leaseStart->lte($currentMonth) && $leaseEnd->gte($currentMonth);
                });

                $tenantCount = $activeLeases->count();
                if ($tenantCount === 0) {
                    $currentMonth->addMonth();
                    continue;
                }

                $utilityData = [];

                // Electricity (always)
                $electricityTotal     = $this->faker->randomFloat(2, 1200, 2500);
                $electricityPerTenant = round($electricityTotal / $tenantCount, 2);
                $utilityData[] = [
                    'unit_id'           => $unit->unit_id,
                    'utility_type'      => 'electricity',
                    'billing_period'    => $currentMonth->format('Y-m-d'),
                    'total_amount'      => $electricityTotal,
                    'tenant_count'      => $tenantCount,
                    'per_tenant_amount' => $electricityPerTenant,
                    'entered_by'        => $unit->manager_id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                // Water (~60% chance)
                if ($this->faker->boolean(60)) {
                    $waterTotal     = $this->faker->randomFloat(2, 200, 500);
                    $waterPerTenant = round($waterTotal / $tenantCount, 2);
                    $utilityData[] = [
                        'unit_id'           => $unit->unit_id,
                        'utility_type'      => 'water',
                        'billing_period'    => $currentMonth->format('Y-m-d'),
                        'total_amount'      => $waterTotal,
                        'tenant_count'      => $tenantCount,
                        'per_tenant_amount' => $waterPerTenant,
                        'entered_by'        => $unit->manager_id,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                // Bulk insert utility bills
                foreach (array_chunk($utilityData, self::CHUNK_SIZE) as $chunk) {
                    UtilityBill::insert($chunk);
                }

                // Prepare billing items
                $billingItems = [];
                foreach ($activeLeases as $lease) {
                    $billing = Billing::where('lease_id', $lease->lease_id)
                        ->where('billing_type', 'monthly')
                        ->whereMonth('billing_date', $currentMonth->month)
                        ->whereYear('billing_date', $currentMonth->year)
                        ->first();

                    if (!$billing) continue;

                    foreach ($utilityData as $util) {
                        $chargeType = $util['utility_type'] === 'electricity' ? 'electricity_share' : 'water_share';
                        $description = $util['utility_type'] === 'electricity'
                            ? "Electricity Share (₱" . number_format($util['total_amount'], 2) . " ÷ {$tenantCount} tenants)"
                            : "Water Share (₱" . number_format($util['total_amount'], 2) . " ÷ {$tenantCount} tenants)";

                        $billingItems[] = [
                            'billing_id'      => $billing->billing_id,
                            'charge_category' => 'recurring',
                            'charge_type'     => $chargeType,
                            'description'     => $description,
                            'amount'          => $util['per_tenant_amount'],
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];

                        // Update billing totals in memory
                        $billing->to_pay += $util['per_tenant_amount'];
                        $billing->amount += $util['per_tenant_amount'];
                    }

                    $billing->save();
                }

                // Bulk insert billing items
                foreach (array_chunk($billingItems, self::CHUNK_SIZE) as $chunk) {
                    BillingItem::insert($chunk);
                }

                $currentMonth->addMonth();
            }
        }

        $this->command->info('✅ Utility bills seeded successfully!');
    }
}
