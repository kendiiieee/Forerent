<?php

namespace Database\Seeders;

use Faker\Generator;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Bed;
use App\Models\Lease;
use Carbon\Carbon;

class LeaseSeeder extends Seeder
{
    protected Generator $faker;

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $tenants = User::where('role', 'tenant')->get();
        $maleTenants   = $tenants->where('gender', 'Male')->values();
        $femaleTenants = $tenants->where('gender', 'Female')->values();

        $availableBeds = Bed::where('status', 'Vacant')->with('unit')->get();
        $managedBeds   = $availableBeds->filter(fn($bed) => !is_null($bed->unit->manager_id))->values();

        if ($managedBeds->isEmpty() || $tenants->isEmpty()) {
            return;
        }

        $bedPool     = $managedBeds->shuffle();
        $tenantCycle = 0;
        $assignedTenantIds = collect();

        foreach ($bedPool as $bed) {
            $tenant = $this->pickTenantForBed($bed->unit->occupants, $tenants, $maleTenants, $femaleTenants, $tenantCycle, $assignedTenantIds);
            if (!$tenant) {
                continue;
            }

            $assignedTenantIds->push($tenant->user_id);

            $unitPrice = (float) $bed->unit->price;

            $this->createLeaseChain($tenant->user_id, $bed, $unitPrice);

            $bed->update(['status' => 'Occupied']);
        }
    }

    private function pickTenantForBed(string $occupantsType, $allTenants, $maleTenants, $femaleTenants, int &$tenantCycle, $assignedTenantIds): ?User
    {
        // Pick from the gender-appropriate pool
        $pool = match ($occupantsType) {
            'Male'   => $maleTenants,
            'Female' => $femaleTenants,
            default  => $allTenants,
        };

        $available = $pool->filter(fn($t) => !$assignedTenantIds->contains($t->user_id));

        if ($available->isEmpty()) {
            // Fallback to any tenant
            $available = $allTenants->filter(fn($t) => !$assignedTenantIds->contains($t->user_id));
        }

        if ($available->isEmpty()) {
            return null;
        }

        $tenant = $available->values()->get($tenantCycle % $available->count());
        $tenantCycle++;

        return $tenant;
    }

    private function createLeaseChain(int $tenantId, Bed $bed, float $unitPrice): void
    {
        $today = Carbon::today();

        $startDate = Carbon::create(2021, 1, 1)
            ->addDays($this->faker->numberBetween(0, Carbon::create(2021, 1, 1)->diffInDays($today)))
            ->startOfMonth();

        while (true) {
            $monthsUntilToday = $startDate->diffInMonths($today);
            $term = $monthsUntilToday <= 3
                ? $this->faker->randomElement([1, 3])
                : $this->faker->randomElement([1, 3, 6, 12]);
            $endDate = $startDate->copy()->addMonths($term);

            $isExpired = $endDate->lt($today);
            $status    = $isExpired ? 'Expired' : 'Active';

            $shortTermPremium = $term < 6 ? 500 : 0;

            Lease::factory()->create([
                'tenant_id'             => $tenantId,
                'bed_id'                => $bed->bed_id,
                'status'                => $status,
                'term'                  => $term,
                'start_date'            => $startDate->toDateString(),
                'end_date'              => $endDate->toDateString(),
                'move_in'               => $startDate->toDateString(),
                'contract_rate'         => $unitPrice,
                'advance_amount'        => $unitPrice,
                'security_deposit'      => $unitPrice,
                'auto_renew'            => $isExpired,
                'short_term_premium'    => $shortTermPremium,
                'shift'                 => 'Morning',
                'monthly_due_date'      => 1,
                'late_payment_penalty'  => 1,
                'reservation_fee_paid'  => 0,
                'early_termination_fee' => 0,
            ]);

            if ($isExpired) {
                $startDate = $endDate->copy();
            } else {
                break;
            }
        }
    }
}
