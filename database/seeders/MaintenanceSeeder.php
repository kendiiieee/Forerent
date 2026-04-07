<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceSeeder extends Seeder
{
    protected Generator $faker;

    private array $categories = [
        'Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control',
    ];

    private array $problems = [
        'Plumbing' => [
            'Leaking faucet', 'Clogged drain', 'Running toilet', 'Low water pressure',
            'Water heater broken', 'Pipe leakage', 'Dripping shower head', 'Sink drainage issue'
        ],
        'Electrical' => [
            'Power outlet not working', 'Light fixture malfunction', 'Circuit breaker tripping',
            'AC unit electrical issues', 'Wiring problem', 'Switch not working', 'Flickering lights', 'Electrical panel inspection'
        ],
        'Structural' => [
            'Wall crack', 'Door frame damage', 'Window frame repair', 'Ceiling crack',
            'Flooring issue', 'Wall damage', 'Loose tiles', 'Door not closing'
        ],
        'Appliance' => [
            'Refrigerator not cooling', 'Oven issue', 'Microwave not heating', 'Washing machine malfunction',
            'Dryer not working', 'Dishwasher leakage', 'AC remote issue', 'Exhaust fan noise'
        ],
        'Pest Control' => [
            'Ant infestation', 'Cockroach sighting', 'Rodent activity', 'Termite inspection',
            'Spider infestation', 'General pest treatment', 'Mosquito problem', 'Bed bug inspection'
        ],
    ];

    public function run(): void
    {
        $this->faker = app(Generator::class);

        // Fetch all relevant leases in one query
        $leases = DB::table('leases')
            ->whereIn('status', ['Active', 'Expired'])
            ->get(['lease_id', 'bed_id', 'status']);

        if ($leases->isEmpty()) {
            $this->command->error('No leases found. Run LeaseSeeder first.');
            return;
        }

        // Map beds to units
        $bedIds = $leases->pluck('bed_id')->unique()->toArray();
        $bedToUnit = DB::table('beds')
            ->whereIn('bed_id', $bedIds)
            ->pluck('unit_id', 'bed_id')
            ->toArray();

        // Group leases by unit for batch maintenance generation
        $leasesByUnit = [];
        foreach ($leases as $lease) {
            $unitId = $bedToUnit[$lease->bed_id] ?? null;
            if ($unitId) {
                $leasesByUnit[$unitId][] = $lease;
            }
        }

        if (empty($leasesByUnit)) {
            $this->command->error('No units found for leases.');
            return;
        }

        $managers = DB::table('users')
            ->where('role', 'manager')
            ->get(['user_id', 'first_name', 'last_name'])
            ->map(fn($u) => ['user_id' => $u->user_id, 'name' => "{$u->first_name} {$u->last_name}"])
            ->toArray();

        if (empty($managers)) {
            $this->command->error('No managers found. Run UserSeeder first.');
            return;
        }

        $requests = [];
        $logs = [];
        $requestId = 1;

        $currentDate = Carbon::create(2021, 1, 1);
        $endDate = Carbon::now();

        while ($currentDate->lte($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth()->min($endDate);

            foreach ($leasesByUnit as $unitId => $unitLeases) {
                $requestsThisUnit = rand(0, 2); // max 2 requests per unit per month

                for ($i = 0; $i < $requestsThisUnit; $i++) {
                    $lease = $unitLeases[array_rand($unitLeases)];
                    $category = $this->categories[array_rand($this->categories)];
                    $problem = $this->problems[$category][array_rand($this->problems[$category])];
                    $urgency = $this->getWeightedUrgency($category);
                    $logDate = $monthStart->copy()->addDays(rand(0, $monthStart->diffInDays($monthEnd)));

                    // Determine status
                    $monthsAgo = $logDate->diffInMonths(Carbon::now());
                    $status = $lease->status === 'Expired' ? 'Completed' : $this->resolveStatus($monthsAgo);

                    $manager = $managers[array_rand($managers)];

                    $requests[] = [
                        'lease_id' => $lease->lease_id,
                        'status' => $status,
                        'logged_by' => $manager['name'],
                        'ticket_number' => 'TICKET-' . str_pad($requestId, 6, '0', STR_PAD_LEFT),
                        'log_date' => $logDate->format('Y-m-d'),
                        'problem' => $problem,
                        'urgency' => $urgency,
                        'category' => $category,
                        'created_at' => $logDate,
                        'updated_at' => $logDate,
                    ];

                    if ($status === 'Completed') {
                        $completionDays = $this->getCompletionDays($category, $urgency);
                        $completionDate = $logDate->copy()->addDays($completionDays)->min($endDate);

                        $logs[] = [
                            'request_id' => $requestId,
                            'completion_date' => $completionDate->format('Y-m-d'),
                            'cost' => $this->calculateCost($category, $logDate->month),
                            'created_at' => $completionDate,
                            'updated_at' => $completionDate,
                        ];
                    }

                    $requestId++;
                }
            }

            $currentDate->addMonth()->startOfMonth();
        }

        foreach (array_chunk($requests, 500) as $chunk) {
            DB::table('maintenance_requests')->insert($chunk);
        }

        foreach (array_chunk($logs, 500) as $chunk) {
            DB::table('maintenance_logs')->insert($chunk);
        }

        $totalCost = array_sum(array_column($logs, 'cost'));
        $this->command->info("✅ Maintenance seeded: " . count($requests) . " requests, total cost: ₱" . number_format($totalCost, 2));
    }

    private function resolveStatus(int $monthsAgo): string
    {
        if ($monthsAgo >= 2) {
            return $this->getWeightedRandom(['Completed' => 0.90, 'Ongoing' => 0.07, 'Pending' => 0.03]);
        }
        if ($monthsAgo === 1) {
            return $this->getWeightedRandom(['Completed' => 0.75, 'Ongoing' => 0.15, 'Pending' => 0.10]);
        }
        return $this->getWeightedRandom(['Completed' => 0.40, 'Ongoing' => 0.35, 'Pending' => 0.25]);
    }

    private function calculateCost(string $category, int $month): float
    {
        $ranges = [
            'Plumbing' => [1500, 8000],
            'Electrical' => [2500, 8000],
            'Structural' => [3000, 8000],
            'Appliance' => [1000, 6000],
            'Pest Control' => [600, 2500],
        ];

        $seasonal = [
            'Plumbing' => [12 => 1.4, 1 => 1.4, 2 => 1.3, 6 => 0.8, 7 => 0.7, 8 => 0.8],
            'Electrical' => [6 => 1.3, 7 => 1.5, 8 => 1.4, 12 => 0.8, 1 => 0.7, 2 => 0.8],
            'Structural' => [3 => 1.2, 4 => 1.1, 9 => 1.2, 10 => 1.1],
            'Appliance' => [],
            'Pest Control' => [3 => 1.2, 6 => 1.2, 9 => 1.2, 12 => 1.2],
        ];

        [$min, $max] = $ranges[$category];
        $factor = $seasonal[$category][$month] ?? 1.0;
        $base = rand($min * 100, $max * 100) / 100;

        return round($base * $factor * (0.8 + rand(0, 40) / 100), 2);
    }

    private function getWeightedUrgency(string $category): string
    {
        $weights = [
            'Plumbing' => ['Level 1' => 0.4, 'Level 2' => 0.3, 'Level 3' => 0.2, 'Level 4' => 0.1],
            'Electrical' => ['Level 1' => 0.5, 'Level 2' => 0.3, 'Level 3' => 0.15, 'Level 4' => 0.05],
            'Structural' => ['Level 1' => 0.3, 'Level 2' => 0.4, 'Level 3' => 0.2, 'Level 4' => 0.1],
            'Appliance' => ['Level 1' => 0.2, 'Level 2' => 0.4, 'Level 3' => 0.3, 'Level 4' => 0.1],
            'Pest Control' => ['Level 1' => 0.1, 'Level 2' => 0.3, 'Level 3' => 0.4, 'Level 4' => 0.2],
        ];

        return $this->getWeightedRandom($weights[$category]);
    }

    private function getWeightedRandom(array $weights): string
    {
        $rand = rand(1, 100) / 100;
        $cumulative = 0;

        foreach ($weights as $item => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $item;
            }
        }

        return array_key_first($weights);
    }

    private function getCompletionDays(string $category, string $urgency): int
    {
        $base = [
            'Plumbing' => [1, 7],
            'Electrical' => [1, 5],
            'Structural' => [5, 20],
            'Appliance' => [1, 7],
            'Pest Control' => [1, 4],
        ];

        $multipliers = ['Level 1' => 0.3, 'Level 2' => 0.6, 'Level 3' => 0.8, 'Level 4' => 1.0];

        [$min, $max] = $base[$category];
        $multiplier = $multipliers[$urgency];

        return rand(max(1, (int)($min * $multiplier)), max(1, (int)($max * $multiplier)));
    }
}
