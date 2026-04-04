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

    public function run(): void
    {
        $this->faker = app(Generator::class);

        // Get units that have active leases
        $units = Unit::whereHas('beds', function ($q) {
            $q->whereHas('leases', function ($q2) {
                $q2->where('status', 'Active');
            });
        })->with(['beds.leases' => function ($q) {
            $q->where('status', 'Active');
        }])->get();

        $today = Carbon::now();

        foreach ($units as $unit) {
            // Count active tenants in this unit
            $activeLeases = $unit->beds->flatMap->leases;
            $activeTenantCount = $activeLeases->count();
            if ($activeTenantCount === 0) continue;

            $managerId = $unit->manager_id;
            if (!$managerId) continue;

            // Generate utility bills for the last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $billingPeriod = $today->copy()->subMonths($i)->startOfMonth();

                // Electricity bill
                $electricityTotal = $this->faker->randomFloat(2, 1200, 2500);
                $electricityPerTenant = round($electricityTotal / $activeTenantCount, 2);

                UtilityBill::create([
                    'unit_id'           => $unit->unit_id,
                    'utility_type'      => 'electricity',
                    'billing_period'    => $billingPeriod->format('Y-m-d'),
                    'total_amount'      => $electricityTotal,
                    'tenant_count'      => $activeTenantCount,
                    'per_tenant_amount' => $electricityPerTenant,
                    'entered_by'        => $managerId,
                ]);

                // Add electricity share to each tenant's billing
                $this->addUtilityToTenantBillings(
                    $activeLeases, $billingPeriod, 'electricity',
                    $electricityTotal, $electricityPerTenant, $activeTenantCount
                );

                // Water bill (~60% chance per month)
                if ($this->faker->boolean(60)) {
                    $waterTotal = $this->faker->randomFloat(2, 200, 500);
                    $waterPerTenant = round($waterTotal / $activeTenantCount, 2);

                    UtilityBill::create([
                        'unit_id'           => $unit->unit_id,
                        'utility_type'      => 'water',
                        'billing_period'    => $billingPeriod->format('Y-m-d'),
                        'total_amount'      => $waterTotal,
                        'tenant_count'      => $activeTenantCount,
                        'per_tenant_amount' => $waterPerTenant,
                        'entered_by'        => $managerId,
                    ]);

                    // Add water share to each tenant's billing
                    $this->addUtilityToTenantBillings(
                        $activeLeases, $billingPeriod, 'water',
                        $waterTotal, $waterPerTenant, $activeTenantCount
                    );
                }
            }
        }
    }

    /**
     * Add a utility billing item to each tenant's monthly billing.
     * Mirrors the logic in UtilityBillEntry::save().
     */
    private function addUtilityToTenantBillings($leases, Carbon $period, string $utilityType, float $totalAmount, float $perTenantAmount, int $tenantCount): void
    {
        $chargeType = $utilityType === 'electricity' ? 'electricity_share' : 'water_share';
        $description = $utilityType === 'electricity'
            ? "Electricity Share (Meralco ₱" . number_format($totalAmount, 2) . " ÷ {$tenantCount} tenants)"
            : "Water Share (₱" . number_format($totalAmount, 2) . " ÷ {$tenantCount} tenants)";

        foreach ($leases as $lease) {
            $billing = Billing::where('lease_id', $lease->lease_id)
                ->where('billing_type', 'monthly')
                ->whereMonth('billing_date', $period->month)
                ->whereYear('billing_date', $period->year)
                ->first();

            if (!$billing) continue;

            BillingItem::create([
                'billing_id'      => $billing->billing_id,
                'charge_category' => 'recurring',
                'charge_type'     => $chargeType,
                'description'     => $description,
                'amount'          => $perTenantAmount,
            ]);

            $billing->update([
                'to_pay' => $billing->to_pay + $perTenantAmount,
                'amount' => $billing->amount + $perTenantAmount,
            ]);
        }
    }
}
