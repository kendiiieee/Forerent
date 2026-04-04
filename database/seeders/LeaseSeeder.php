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
        $maleTenants = $tenants->where('gender', 'Male')->values();
        $femaleTenants = $tenants->where('gender', 'Female')->values();

        $availableBeds = Bed::where('status', 'Vacant')->with('unit')->get();
        $managedBeds = $availableBeds->filter(fn($bed) => !is_null($bed->unit->manager_id))->values();

        if ($managedBeds->isEmpty() || $tenants->isEmpty()) {
            return;
        }

        // Target occupancy: at least 60% of currently available managed beds.
        $targetOccupiedCount = (int) ceil($managedBeds->count() * 0.60);
        $bedsToOccupy = $managedBeds->shuffle()->take($targetOccupiedCount);

        $tenantCycle = 0;
        $assignedTenantIds = collect();

        foreach ($bedsToOccupy as $bed) {
            $tenant = $this->pickTenantForBed($bed->unit->occupants, $tenants, $maleTenants, $femaleTenants, $tenantCycle, $assignedTenantIds);
            if (!$tenant) {
                continue;
            }

            $assignedTenantIds->push($tenant->user_id);

            $unitPrice = (float) $bed->unit->price;
            $term = $this->faker->numberBetween(9, 18);
            $startDate = Carbon::now()->subMonths($this->faker->numberBetween(1, 8))->startOfMonth();
            $endDate = $startDate->copy()->addMonths($term);

            Lease::factory()->create([
                'tenant_id'        => $tenant->user_id,
                'bed_id'           => $bed->bed_id,
                'status'           => 'Active',
                'term'             => $term,
                'start_date'       => $startDate->toDateString(),
                'end_date'         => $endDate->toDateString(),
                'move_in'          => $startDate->toDateString(),
                'contract_rate'    => $unitPrice,
                // Keep advance/deposit tied to full lease amount.
                'advance_amount'   => $unitPrice,
                'security_deposit' => $unitPrice,
            ]);

            $bed->update(['status' => 'Occupied']);
        }
    }

    private function pickTenantForBed(string $occupantsType, $allTenants, $maleTenants, $femaleTenants, int &$tenantCycle, $assignedTenantIds)
    {
        $pool = match ($occupantsType) {
            'Male' => $maleTenants,
            'Female' => $femaleTenants,
            default => $allTenants,
        };

        if ($pool->isEmpty()) {
            $pool = $allTenants;
        }

        // Filter out already-assigned tenants
        $available = $pool->filter(fn($t) => !$assignedTenantIds->contains($t->user_id));

        if ($available->isEmpty()) {
            return null;
        }

        $tenant = $available->first();
        $tenantCycle++;

        return $tenant;
    }
}
