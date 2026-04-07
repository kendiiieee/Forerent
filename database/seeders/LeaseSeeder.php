<?php

namespace Database\Seeders;

use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Bed;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaseSeeder extends Seeder
{
    private Generator $faker;
    private Carbon $today;
    private int $activeLeaseCount = 0;
    private string $localIp = '127.0.0.1'; // <--- IP variable

    /** @var array<string, string[]> Pre-generated pool: ['owner' => [...5 paths], 'manager' => [...], 'tenant' => [...]] */
    private array $signaturePool = [];

    public function run(): void
    {
        $this->faker = app(Generator::class);
        $this->today = Carbon::today();

        $tenants = User::where('role', 'tenant')->get();

        if ($tenants->isEmpty()) {
            return;
        }

        $managedBeds = Bed::where('status', 'Vacant')
            ->with('unit')
            ->get()
            ->filter(fn(Bed $bed) => !is_null($bed->unit?->manager_id))
            ->values();

        if ($managedBeds->isEmpty()) {
            return;
        }

        // Generate exactly 5 signature files per role (15 total) once — zero per-lease I/O
        $this->pregenerateSignaturePool();

        $maleTenants   = $tenants->where('gender', 'Male')->values();
        $femaleTenants = $tenants->where('gender', 'Female')->values();

        $assignedTenantIds = collect();
        $tenantCycle       = 0;
        $leaseRows         = [];
        $occupiedBedIds    = [];

        DB::transaction(function () use (
            $managedBeds, $tenants, $maleTenants, $femaleTenants,
            &$assignedTenantIds, &$tenantCycle, &$leaseRows, &$occupiedBedIds
        ) {
            foreach ($managedBeds->shuffle() as $bed) {
                $tenant = $this->pickTenantForBed(
                    $bed->unit->occupants,
                    $tenants,
                    $maleTenants,
                    $femaleTenants,
                    $tenantCycle,
                    $assignedTenantIds
                );

                if (!$tenant) {
                    continue;
                }

                $assignedTenantIds->push($tenant->user_id);
                $occupiedBedIds[] = $bed->bed_id;

                array_push($leaseRows, ...$this->buildLeaseChainRows($tenant->user_id, $bed, (float) $bed->unit->price));
            }

            // Single bulk insert instead of one INSERT per lease
            foreach (array_chunk($leaseRows, 500) as $chunk) {
                Lease::insert($chunk);
            }

            // Single UPDATE instead of one per bed
            if (!empty($occupiedBedIds)) {
                Bed::whereIn('bed_id', $occupiedBedIds)->update(['status' => 'Occupied']);
            }
        });
    }

    // ─── Signature pool ───────────────────────────────────────────────────────

    private function pregenerateSignaturePool(): void
    {
        foreach (['owner', 'manager', 'tenant'] as $role) {
            $this->signaturePool[$role] = [];

            foreach (range(1, 5) as $i) {
                $filename = "signatures/seed_{$role}_{$i}.svg";
                Storage::disk('local')->put($filename, $this->buildSvg());
                $this->signaturePool[$role][] = $filename;
            }
        }
    }

    private function randomSignature(string $role): string
    {
        $pool = $this->signaturePool[$role];
        return $pool[array_rand($pool)];
    }

    // ─── Tenant picking ───────────────────────────────────────────────────────

    private function pickTenantForBed(
        string $occupantsType,
        Collection $allTenants,
        Collection $maleTenants,
        Collection $femaleTenants,
        int &$tenantCycle,
        Collection $assignedTenantIds
    ): ?User {
        $pool = match ($occupantsType) {
            'Male'   => $maleTenants,
            'Female' => $femaleTenants,
            default  => $allTenants,
        };

        $available = $pool->filter(fn($t) => !$assignedTenantIds->contains($t->user_id))->values();

        if ($available->isEmpty()) {
            $available = $allTenants->filter(fn($t) => !$assignedTenantIds->contains($t->user_id))->values();
        }

        if ($available->isEmpty()) {
            return null;
        }

        return $available->get($tenantCycle++ % $available->count());
    }

    // ─── Lease chain builder ──────────────────────────────────────────────────

    private function buildLeaseChainRows(int $tenantId, Bed $bed, float $unitPrice): array
    {
        $chainStart = Carbon::create(2021, 1, 1);
        $startDate  = $chainStart
            ->copy()
            ->addDays($this->faker->numberBetween(0, $chainStart->diffInDays($this->today)))
            ->startOfMonth();

        $now  = now()->toDateTimeString();
        $rows = [];

        while (true) {
            $monthsUntilToday = $startDate->diffInMonths($this->today);
            $term = $monthsUntilToday <= 3
                ? $this->faker->randomElement([1, 3])
                : $this->faker->randomElement([1, 3, 6, 12]);

            $endDate   = $startDate->copy()->addMonths($term);
            $isExpired = $endDate->lt($this->today);
            $signedAt  = $startDate->copy()->subDays($this->faker->numberBetween(1, 7))->toDateTimeString();

            $rows[] = array_merge([
                'tenant_id'             => $tenantId,
                'bed_id'                => $bed->bed_id,
                'status'                => $isExpired ? 'Expired' : 'Active',
                'term'                  => $term,
                'start_date'            => $startDate->toDateString(),
                'end_date'              => $endDate->toDateString(),
                'move_in'               => $startDate->toDateString(),
                'contract_rate'         => $unitPrice,
                'advance_amount'        => $unitPrice,
                'security_deposit'      => $unitPrice,
                'auto_renew'            => $isExpired ? 1 : 0,
                'short_term_premium'    => $term < 6 ? 500 : 0,
                'shift'                 => 'Morning',
                'monthly_due_date'      => 1,
                'late_payment_penalty'  => 1,
                'reservation_fee_paid'  => 0,
                'early_termination_fee' => 0,
                'created_at'            => $now,
                'updated_at'            => $now,
            ], $this->resolveContractStatus($isExpired, $signedAt));

            if ($isExpired) {
                $startDate = $endDate->copy();
            } else {
                $this->activeLeaseCount++;
                break;
            }
        }

        return $rows;
    }

    // ─── Contract status ──────────────────────────────────────────────────────

    private function resolveContractStatus(bool $isExpired, string $signedAt): array
    {
        if ($isExpired) {
            return $this->executedContract($signedAt);
        }

        return match ($this->activeLeaseCount % 3) {
            0 => $this->draftContract(),
            1 => $this->pendingContract($signedAt),
            default => $this->executedContract($signedAt),
        };
    }

    private function draftContract(): array
    {
        return [
            'contract_status'   => 'draft',
            'contract_agreed'   => 0,
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

    private function pendingContract(string $signedAt): array
    {
        return [
            'contract_status'   => $this->faker->randomElement(['pending_owner', 'pending_signatures']),
            'contract_agreed'   => 1,
            'tenant_signed_at'  => $signedAt,
            'owner_signed_at'   => null,
            'manager_signed_at' => null,
            'tenant_signed_ip'  => $this->localIp, // <--- use variable
            'owner_signed_ip'   => null,
            'manager_signed_ip' => null,
            'owner_signature'   => null,
            'manager_signature' => null,
            'tenant_signature'  => $this->randomSignature('tenant'),
        ];
    }

    private function executedContract(string $signedAt): array
    {
        return [
            'contract_status'   => 'executed',
            'contract_agreed'   => 1,
            'tenant_signed_at'  => $signedAt,
            'owner_signed_at'   => $signedAt,
            'manager_signed_at' => $signedAt,
            'tenant_signed_ip'  => $this->localIp, // <--- use variable
            'owner_signed_ip'   => $this->localIp, // <--- use variable
            'manager_signed_ip' => $this->localIp, // <--- use variable
            'owner_signature'   => $this->randomSignature('owner'),
            'manager_signature' => $this->randomSignature('manager'),
            'tenant_signature'  => $this->randomSignature('tenant'),
        ];
    }

    // ─── SVG generation ───────────────────────────────────────────────────────

    private function buildSvg(): string
    {
        $x    = $this->faker->numberBetween(10, 40);
        $y    = $this->faker->numberBetween(40, 80);
        $path = "M{$x},{$y}";

        foreach (range(1, $this->faker->numberBetween(4, 7)) as $_) {
            $cx   = $x + $this->faker->numberBetween(10, 30);
            $cy   = $this->faker->numberBetween(20, 110);
            $x   += $this->faker->numberBetween(25, 50);
            $y    = $this->faker->numberBetween(20, 110);
            $path .= " Q{$cx},{$cy} {$x},{$y}";
        }

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="300" height="150" viewBox="0 0 300 150">
            <path d="{$path}" fill="none" stroke="#141450" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        SVG;
    }
}
