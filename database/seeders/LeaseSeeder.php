<?php

namespace Database\Seeders;

use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
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

    private int $activeLeaseCount = 0;

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

            $signedAt = $startDate->copy()->subDays($this->faker->numberBetween(1, 7));
            $sigId = uniqid();

            // For active leases, vary the contract status so we get draft/pending/signed
            $contractFields = $this->resolveContractStatus($isExpired, $signedAt, $sigId);

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
                ...$contractFields,
            ]);

            if ($isExpired) {
                $startDate = $endDate->copy();
            } else {
                $this->activeLeaseCount++;
                break;
            }
        }
    }

    /**
     * For expired leases: always executed (historical).
     * For active leases: rotate between draft, pending, and executed.
     */
    private function resolveContractStatus(bool $isExpired, Carbon $signedAt, string $sigId): array
    {
        // Expired leases are always fully signed
        if ($isExpired) {
            return [
                'contract_status'  => 'executed',
                'contract_agreed'  => true,
                'tenant_signed_at' => $signedAt,
                'owner_signed_at'  => $signedAt,
                'manager_signed_at'=> $signedAt,
                'tenant_signed_ip' => '127.0.0.1',
                'owner_signed_ip'  => '127.0.0.1',
                'manager_signed_ip'=> '127.0.0.1',
                'owner_signature'  => $this->generateSignatureImage($sigId, 'owner'),
                'manager_signature'=> $this->generateSignatureImage($sigId, 'manager'),
                'tenant_signature' => $this->generateSignatureImage($sigId, 'tenant'),
            ];
        }

        // Rotate active leases: every 3rd = draft, every 3rd = pending, rest = executed
        $variant = $this->activeLeaseCount % 3;

        if ($variant === 0) {
            // Draft — no signatures at all
            return [
                'contract_status'   => 'draft',
                'contract_agreed'   => false,
                'tenant_signed_at'  => null,
                'owner_signed_at'   => null,
                'manager_signed_at' => null,
                'tenant_signed_ip'  => null,
                'owner_signed_ip'   => null,
                'manager_signed_ip' => null,
                'owner_signature'   => null,
                'manager_signature' => null,
                'tenant_signature'  => null,
            ];
        }

        if ($variant === 1) {
            // Pending — tenant signed, waiting on owner & manager
            $pendingStatus = $this->faker->randomElement([
                'pending_owner',
                'pending_signatures',
            ]);
            return [
                'contract_status'   => $pendingStatus,
                'contract_agreed'   => true,
                'tenant_signed_at'  => $signedAt,
                'owner_signed_at'   => null,
                'manager_signed_at' => null,
                'tenant_signed_ip'  => '127.0.0.1',
                'owner_signed_ip'   => null,
                'manager_signed_ip' => null,
                'owner_signature'   => null,
                'manager_signature' => null,
                'tenant_signature'  => $this->generateSignatureImage($sigId, 'tenant'),
            ];
        }

        // Executed — fully signed
        return [
            'contract_status'  => 'executed',
            'contract_agreed'  => true,
            'tenant_signed_at' => $signedAt,
            'owner_signed_at'  => $signedAt,
            'manager_signed_at'=> $signedAt,
            'tenant_signed_ip' => '127.0.0.1',
            'owner_signed_ip'  => '127.0.0.1',
            'manager_signed_ip'=> '127.0.0.1',
            'owner_signature'  => $this->generateSignatureImage($sigId, 'owner'),
            'manager_signature'=> $this->generateSignatureImage($sigId, 'manager'),
            'tenant_signature' => $this->generateSignatureImage($sigId, 'tenant'),
        ];
    }

    private function generateSignatureImage(string $id, string $role): string
    {
        // Build a random scribble path for the SVG
        $x = $this->faker->numberBetween(10, 40);
        $y = $this->faker->numberBetween(40, 80);
        $path = "M{$x},{$y}";
        $points = $this->faker->numberBetween(4, 7);
        for ($i = 0; $i < $points; $i++) {
            $cx = $x + $this->faker->numberBetween(10, 30);
            $cy = $this->faker->numberBetween(20, 110);
            $x = $x + $this->faker->numberBetween(25, 50);
            $y = $this->faker->numberBetween(20, 110);
            $path .= " Q{$cx},{$cy} {$x},{$y}";
        }

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="300" height="150" viewBox="0 0 300 150">
            <path d="{$path}" fill="none" stroke="#141450" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        SVG;

        $filename = "signatures/{$id}_{$role}.svg";
        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }
}
