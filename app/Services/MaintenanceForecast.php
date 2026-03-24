<?php
// app/Services/MaintenanceForecast.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\Response;

class MaintenanceForecast
{
    protected $apiBaseUrl;

    public function __construct()
    {
        // Use FASTAPI_URL instead of PYTHON_API_URL
        $this->apiBaseUrl = env('FASTAPI_URL', 'http://localhost:8000');
        Log::info('API Base URL: ' . $this->apiBaseUrl);
    }

    public function generateForecast($year, $maintenanceData)
    {
        try {
            $csvData = $this->convertToCsv($maintenanceData);

            Log::info("Sending maintenance forecast request for year: {$year}");

            /** @var Response $response */
            $response = Http::timeout(120)->post($this->apiBaseUrl . '/api/forecast/maintenance', [
                'csv_data' => $csvData,
                'year' => $year
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info("Maintenance forecast API response received", $result);
                return $result;
            } else {
                $error = $this->formatApiError($response->status(), $response->body());
                Log::error($error);
                throw new \Exception($error);
            }
        } catch (\Exception $e) {
            Log::error('Maintenance forecast service error: ' . $e->getMessage());

            // Return a fallback response so the UI remains usable during API outages.
            return $this->buildFallbackForecast((int)$year, (array)$maintenanceData, $e->getMessage());
        }
    }

    private function formatApiError(int $status, string $body): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');

        if ($normalized === '') {
            $normalized = 'No response body';
        }

        $short = mb_substr($normalized, 0, 220);

        return "Maintenance forecast API error ({$status}): {$short}";
    }

    private function buildFallbackForecast(int $year, array $maintenanceData, string $reason): array
    {
        $monthlyAgg = [];

        foreach ($maintenanceData as $row) {
            $date = isset($row['date']) ? (string)$row['date'] : null;
            $cost = isset($row['cost']) ? (float)$row['cost'] : 0.0;
            $urgency = isset($row['urgency']) ? (string)$row['urgency'] : null;

            if (!$date) {
                continue;
            }

            $timestamp = strtotime($date);
            if ($timestamp === false) {
                continue;
            }

            $monthKey = date('Y-m', $timestamp);

            if (!isset($monthlyAgg[$monthKey])) {
                $monthlyAgg[$monthKey] = [
                    'cost' => 0.0,
                    'count' => 0,
                    'urgency_sum' => 0.0,
                ];
            }

            $monthlyAgg[$monthKey]['cost'] += $cost;
            $monthlyAgg[$monthKey]['count'] += 1;
            $monthlyAgg[$monthKey]['urgency_sum'] += $this->urgencyToScore($urgency);
        }

        $monthBuckets = max(1, count($monthlyAgg));
        $totalCost = array_sum(array_column($monthlyAgg, 'cost'));
        $totalJobs = array_sum(array_column($monthlyAgg, 'count'));
        $totalUrgency = array_sum(array_column($monthlyAgg, 'urgency_sum'));

        $avgMonthlyCost = $totalCost / $monthBuckets;
        $avgMonthlyJobs = $totalJobs / $monthBuckets;
        $avgUrgency = $totalJobs > 0 ? ($totalUrgency / $totalJobs) : 0.0;

        $monthlyForecasts = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthlyForecasts[] = [
                'year' => $year,
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
                'forecasted_cost' => round($avgMonthlyCost, 2),
                'maintenance_count_estimate' => (int)max(0, round($avgMonthlyJobs)),
                'urgency_estimate' => round($avgUrgency, 2),
                'seasonal_factor' => 1.0,
            ];
        }

        return [
            'success' => true,
            'is_fallback' => true,
            'warning' => 'Using fallback forecast due to API unavailability.',
            'error' => $reason,
            'forecast_year' => $year,
            'monthly_forecasts' => $monthlyForecasts,
            'maintenance_schedule' => [],
            'total_annual_cost' => round($avgMonthlyCost * 12, 2),
            'total_remaining_cost' => round($avgMonthlyCost * 12, 2),
            'average_monthly_cost' => round($avgMonthlyCost, 2),
            'data_points_used' => (int)count($maintenanceData),
            'model_performance' => [
                'r2_score' => 0,
                'mae' => 0,
                'mape' => 0,
                'clusters_used' => 0,
            ],
        ];
    }

    private function urgencyToScore(?string $urgency): float
    {
        $level = strtoupper((string)$urgency);

        if ($level === 'HIGH') {
            return 3.0;
        }

        if ($level === 'MEDIUM') {
            return 2.0;
        }

        if ($level === 'LOW') {
            return 1.0;
        }

        return 0.0;
    }

    private function convertToCsv($data)
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Check if data is in monthly format or individual record format
        $firstRow = $data[0];

        if (isset($firstRow['monthly_maintenance_cost'])) {
            // Monthly aggregated format
            $headers = ['year', 'month', 'monthly_maintenance_cost', 'maintenance_count', 'urgency_score'];
        } else {
            // Individual record format (original)
            $headers = array_keys($firstRow);
        }

        // Write headers
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            if (isset($firstRow['monthly_maintenance_cost'])) {
                // Write only the monthly aggregated fields
                fputcsv($output, [
                    $row['year'] ?? '',
                    $row['month'] ?? '',
                    $row['monthly_maintenance_cost'] ?? '',
                    $row['maintenance_count'] ?? '',
                    $row['urgency_score'] ?? ''
                ]);
            } else {
                // Write all fields (original format)
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        Log::info('CSV data prepared', [
            'rows' => count($data),
            'csv_length' => strlen($csv),
            'format' => isset($firstRow['monthly_maintenance_cost']) ? 'monthly' : 'individual'
        ]);

        return $csv;
    }

    public function getMaintenanceDataForForecast()
    {
        // Fetch ALL raw, individual completed logs from all time.
        // The Python API needs the full history to do the aggregation itself.
        $rawData = DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->select(
                'ml.log_id',
                'ml.completion_date as date', // Python will use this for the date
                'ml.cost',                    // Python will use this for cost
                'mr.urgency',                  // Python will use this
                'mr.category'
            )
            ->where('mr.status', 'Completed')
            ->whereNotNull('ml.completion_date')
            ->whereNotNull('ml.cost')
            ->orderBy('ml.completion_date')
            ->get()
            ->map(function ($item) {
                // Convert stdClass to array
                return (array)$item;
            })
            ->toArray();

        Log::info('Sending ' . count($rawData) . ' total raw records to API for training.');

        return $rawData;
    }
    private function getMonthlyDateRange($monthlyData)
    {
        if (empty($monthlyData)) {
            return 'No data';
        }

        $first = $monthlyData[0];
        $last = $monthlyData[count($monthlyData) - 1];

        return $first['year'] . '-' . $first['month'] . ' to ' . $last['year'] . '-' . $last['month'];
    }

    public function getMaintenanceStats()
    {
        $stats = DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->select(
                DB::raw('COUNT(*) as total_records'),
                DB::raw('MIN(mr.log_date) as earliest_date'),
                DB::raw('MAX(mr.log_date) as latest_date'),
                DB::raw('SUM(ml.cost) as total_cost'),
                DB::raw('AVG(ml.cost) as avg_cost')
            )
            ->where('mr.status', 'Completed')
            ->first();

        return [
            'total_records' => $stats->total_records ?? 0,
            'date_range' => ($stats->earliest_date ?? 'N/A') . ' to ' . ($stats->latest_date ?? 'N/A'),
            'total_cost' => $stats->total_cost ?? 0,
            'avg_monthly_cost' => $stats->avg_cost ?? 0
        ];
    }

    private function inferCategoryFromProblem($requestId)
    {
        // Temporary solution - you should add a 'category' column to maintenance_request table
        $categories = ['Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control'];
        return $categories[array_rand($categories)];
    }
}
