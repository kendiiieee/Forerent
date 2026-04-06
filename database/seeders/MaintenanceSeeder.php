<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceSeeder extends Seeder
{
    protected Generator $faker;

    private array $maintenanceCategories = [
        'Plumbing',
        'Electrical',
        'Structural',
        'Appliance',
        'Pest Control',
    ];

    private array $problems = [
        'Plumbing' => [
            'Leaking faucet in bathroom',
            'Clogged drain in kitchen sink',
            'Running toilet in unit',
            'Low water pressure in shower',
            'Water heater not working',
            'Pipe leakage under sink',
            'Dripping shower head needs replacement',
            'Sink drainage issue in common area',
        ],
        'Electrical' => [
            'Power outlet not working',
            'Light fixture malfunction',
            'Circuit breaker tripping',
            'AC unit electrical issues',
            'Wiring problem in bedroom',
            'Switch not functioning',
            'Flickering lights in hallway',
            'Electrical panel inspection needed',
        ],
        'Structural' => [
            'Crack in wall needs repair',
            'Door frame damage',
            'Window frame repair needed',
            'Ceiling crack inspection',
            'Flooring issue in living room',
            'Wall damage from moisture',
            'Loose floor tiles in bathroom',
            'Door not closing properly',
        ],
        'Appliance' => [
            'Refrigerator not cooling',
            'Oven temperature inaccurate',
            'Microwave not heating',
            'Washing machine malfunction',
            'Dryer not working properly',
            'Dishwasher leakage',
            'AC remote control not working',
            'Exhaust fan making noise',
        ],
        'Pest Control' => [
            'Ant infestation in kitchen',
            'Cockroach sighting in bathroom',
            'Rodent activity detected',
            'Termite inspection needed',
            'Spider infestation in corners',
            'General pest control treatment',
            'Mosquito problem near windows',
            'Bed bug inspection requested',
        ],
    ];

    public function run(): void
    {
        $this->faker = app(Generator::class);

        $allLeases = DB::table('leases')
            ->whereIn('status', ['Active', 'Expired'])
            ->get(['lease_id', 'status', 'bed_id']);

        $expiredLeases = $allLeases->where('status', 'Expired')->pluck('lease_id')->flip()->toArray();

        // Group leases by unit via beds
        $bedIds = $allLeases->pluck('bed_id')->unique()->toArray();
        $bedToUnit = DB::table('beds')
            ->whereIn('bed_id', $bedIds)
            ->pluck('unit_id', 'bed_id')
            ->toArray();

        // Group leases by unit_id
        $leasesByUnit = [];
        foreach ($allLeases as $lease) {
            $unitId = $bedToUnit[$lease->bed_id] ?? null;
            if ($unitId) {
                $leasesByUnit[$unitId][] = $lease->lease_id;
            }
        }

        $managerNames = DB::table('users')
            ->where('role', 'manager')
            ->get(['first_name', 'last_name'])
            ->map(fn($u) => "{$u->first_name} {$u->last_name}")
            ->toArray();

        if (empty($leasesByUnit)) {
            $this->command->error('No leases found. Run LeaseSeeder first.');
            return;
        }

        if (empty($managerNames)) {
            $this->command->error('No managers found. Run UserSeeder first.');
            return;
        }

        $maintenanceRequests = [];
        $maintenanceLogs     = [];
        $requestId           = 1;

        $currentDate = Carbon::create(2021, 1, 1);
        $endDate     = Carbon::now();

        while ($currentDate->lte($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd   = $currentDate->copy()->endOfMonth()->min($endDate);

            // Each unit gets its own random request count this month
            foreach ($leasesByUnit as $unitId => $unitLeaseIds) {
                $requestsThisUnit = rand(0, 2); // 0–2 requests per unit per month

                for ($i = 0; $i < $requestsThisUnit; $i++) {
                    $category = $this->maintenanceCategories[array_rand($this->maintenanceCategories)];
                    $urgency  = $this->getWeightedUrgency($category);
                    $leaseId  = $unitLeaseIds[array_rand($unitLeaseIds)];
                    $logDate  = $monthStart->copy()->addDays(rand(0, $monthStart->diffInDays($monthEnd)));

                    $monthsAgo = $logDate->diffInMonths(Carbon::now());
                    $status    = isset($expiredLeases[$leaseId])
                        ? 'Completed'
                        : $this->resolveStatus($monthsAgo);

                    $maintenanceRequests[] = [
                        'lease_id'      => $leaseId,
                        'status'        => $status,
                        'logged_by'     => $managerNames[array_rand($managerNames)],
                        'ticket_number' => 'TICKET-' . str_pad($requestId, 6, '0', STR_PAD_LEFT),
                        'log_date'      => $logDate->format('Y-m-d'),
                        'problem'       => $this->problems[$category][array_rand($this->problems[$category])],
                        'urgency'       => $urgency,
                        'category'      => $category,
                        'created_at'    => $logDate->format('Y-m-d H:i:s'),
                        'updated_at'    => $logDate->format('Y-m-d H:i:s'),
                    ];

                    if ($status === 'Completed') {
                        $completionDays = $this->getCompletionDays($category, $urgency);
                        $completionDate = $logDate->copy()->addDays($completionDays)->min($endDate);

                        $maintenanceLogs[] = [
                            'request_id'      => $requestId,
                            'completion_date' => $completionDate->format('Y-m-d'),
                            'cost'            => $this->calculateCost($category, $logDate->month),
                            'created_at'      => $completionDate->format('Y-m-d H:i:s'),
                            'updated_at'      => $completionDate->format('Y-m-d H:i:s'),
                        ];
                    }

                    $requestId++;
                }
            }

            $currentDate->addMonth()->startOfMonth();
        }

        $this->command->info("Inserting " . count($maintenanceRequests) . " maintenance requests...");
        foreach (array_chunk($maintenanceRequests, 500) as $chunk) {
            DB::table('maintenance_requests')->insert($chunk);
        }

        $this->command->info("Inserting " . count($maintenanceLogs) . " maintenance logs...");
        foreach (array_chunk($maintenanceLogs, 500) as $chunk) {
            DB::table('maintenance_logs')->insert($chunk);
        }

        $totalCost = array_sum(array_column($maintenanceLogs, 'cost'));
        $this->command->info("✅ Successfully seeded maintenance data!");
        $this->command->info("📊 Total Maintenance Cost: ₱" . number_format($totalCost, 2));
    }

    private function resolveStatus(int $monthsAgo): string
    {
        if ($monthsAgo >= 2) {
            return $this->getWeightedRandom(['Completed' => 0.90, 'Ongoing' => 0.07, 'Pending' => 0.03]);
        }

        if ($monthsAgo === 1) {
            return $this->getWeightedRandom(['Completed' => 0.75, 'Ongoing' => 0.15, 'Pending' => 0.10]);
        }

        // Current month
        return $this->getWeightedRandom(['Completed' => 0.40, 'Ongoing' => 0.35, 'Pending' => 0.25]);
    }

    private function calculateCost(string $category, int $month): float
    {
        $ranges = [
            'Plumbing'     => [1500, 8000],
            'Electrical'   => [2500, 8000],
            'Structural'   => [3000, 8000],
            'Appliance'    => [1000, 6000],
            'Pest Control' => [600,  2500],
        ];

        $seasonalFactors = [
            'Plumbing'     => [12 => 1.4, 1 => 1.4, 2 => 1.3, 6 => 0.8, 7 => 0.7, 8 => 0.8],
            'Electrical'   => [6 => 1.3, 7 => 1.5, 8 => 1.4, 12 => 0.8, 1 => 0.7, 2 => 0.8],
            'Structural'   => [3 => 1.2, 4 => 1.1, 9 => 1.2, 10 => 1.1],
            'Appliance'    => [],
            'Pest Control' => [3 => 1.2, 6 => 1.2, 9 => 1.2, 12 => 1.2],
        ];

        [$min, $max]    = $ranges[$category];
        $seasonalFactor = $seasonalFactors[$category][$month] ?? 1.0;
        $baseCost       = rand($min * 100, $max * 100) / 100;

        return round($baseCost * $seasonalFactor * (0.8 + rand(0, 40) / 100), 2);
    }

    private function getWeightedUrgency(string $category): string
    {
        $weights = [
            'Plumbing'     => ['Level 1' => 0.4, 'Level 2' => 0.3, 'Level 3' => 0.2, 'Level 4' => 0.1],
            'Electrical'   => ['Level 1' => 0.5, 'Level 2' => 0.3, 'Level 3' => 0.15, 'Level 4' => 0.05],
            'Structural'   => ['Level 1' => 0.3, 'Level 2' => 0.4, 'Level 3' => 0.2, 'Level 4' => 0.1],
            'Appliance'    => ['Level 1' => 0.2, 'Level 2' => 0.4, 'Level 3' => 0.3, 'Level 4' => 0.1],
            'Pest Control' => ['Level 1' => 0.1, 'Level 2' => 0.3, 'Level 3' => 0.4, 'Level 4' => 0.2],
        ];

        return $this->getWeightedRandom($weights[$category]);
    }

    private function getWeightedRandom(array $weights): string
    {
        $random     = rand(1, 100) / 100;
        $cumulative = 0;

        foreach ($weights as $item => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $item;
            }
        }

        return array_key_first($weights);
    }

    private function getCompletionDays(string $category, string $urgency): int
    {
        $baseRanges = [
            'Plumbing'     => [1, 7],
            'Electrical'   => [1, 5],
            'Structural'   => [5, 20],
            'Appliance'    => [1, 7],
            'Pest Control' => [1, 4],
        ];

        $urgencyMultipliers = [
            'Level 1' => 0.3,
            'Level 2' => 0.6,
            'Level 3' => 0.8,
            'Level 4' => 1.0,
        ];

        [$min, $max] = $baseRanges[$category];
        $multiplier  = $urgencyMultipliers[$urgency];

        return rand(max(1, (int) ($min * $multiplier)), max(1, (int) ($max * $multiplier)));
    }
}
