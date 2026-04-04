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

        // Every tenant gets at least one lease
        foreach ($tenants as $tenant) {
            if ($bedPool->isEmpty()) {
                break;
            }

            // Find a compatible bed for this tenant
            $bedIndex = $this->findCompatibleBedIndex($bedPool, $tenant->gender);
            if ($bedIndex === null) {
                continue;
            }

            $bed = $bedPool->splice($bedIndex, 1)->first();
            $unitPrice = (float) $bed->unit->price;

            // Build a chain of leases starting from a random date in 2021 up to today
            $this->createLeaseChain($tenant->user_id, $bed, $unitPrice);

            $bed->update(['status' => 'Occupied']);
        }
    }

    /**
     * Creates a chain of leases for a tenant starting from 2021.
     * If a lease expires before today, a renewal is created starting from the expiry date.
     * The final/active lease will have status 'Active'.
     */
    private function createLeaseChain(int $tenantId, Bed $bed, float $unitPrice): void
    {
        $today = Carbon::today();

        // Random start date between Jan 2021 and today
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

            // Calculate short-term premium: $500 if term < 6 months, otherwise 0
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
                // Add other required fields with defaults if they're required in your database
                'shift'                 => 'Morning', // or appropriate default
                'monthly_due_date'      => 1, // or appropriate default (day of month)
                'late_payment_penalty'  => 100,
                'reservation_fee_paid'  => 0,
                'early_termination_fee' => 0,
            ]);

            // If expired, renew — next lease starts exactly when the last one ended
            if ($isExpired) {
                $startDate = $endDate->copy();
            } else {
                // Active lease created, chain is complete
                break;
            }
        }
    }
    /**
     * Finds the index of the first bed in the pool that matches the tenant's gender occupancy.
     * Falls back to any bed if no gender match is found.
     */
    private function findCompatibleBedIndex($bedPool, string $gender): ?int
    {
        // Try to find a gender-matching bed first
        foreach ($bedPool as $index => $bed) {
            $occupants = $bed->unit->occupants;
            if ($occupants === $gender || $occupants === 'Any') {
                return $index;
            }
        }

        // Fall back to any available bed
        return $bedPool->isNotEmpty() ? 0 : null;
    }
}
