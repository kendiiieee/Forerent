<?php

// app/Services/MaintenanceForecast.php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MaintenanceForecast
{
    protected $apiBaseUrl;

    private int $timeoutSeconds;

    private int $retryAttempts;

    private int $retryBaseDelayMs;

    private int $forecastCacheTtlSeconds;

    public function __construct()
    {
        $this->apiBaseUrl = $this->resolveApiBaseUrl();
        $this->timeoutSeconds = (int) env('FORECAST_API_TIMEOUT_SECONDS', 120);
        $this->retryAttempts = max(1, (int) env('FORECAST_API_RETRY_ATTEMPTS', 3));
        $this->retryBaseDelayMs = max(100, (int) env('FORECAST_API_RETRY_BASE_DELAY_MS', 400));
        $this->forecastCacheTtlSeconds = max(60, (int) env('FORECAST_CACHE_TTL_SECONDS', 900));
        Log::info('API Base URL: '.$this->apiBaseUrl);
    }

    private function resolveApiBaseUrl(): string
    {
        return (string) (
            env('FASTAPI_URL')
            ?: env('PYTHON_API_URL')
            ?: env('PRICE_API_URL')
            ?: 'http://localhost:8000'
        );
    }

    public function generateForecast($year, $maintenanceData)
    {
        try {
            $year = (int) $year;
            $dataSignature = $this->getTrainingDataSignature();
            $cacheKey = $this->buildForecastCacheKey($year, $dataSignature);
            $cachedForecast = Cache::get($cacheKey);

            if (is_array($cachedForecast)) {
                Log::info('Maintenance forecast cache hit', [
                    'year' => $year,
                    'cache_key' => $cacheKey,
                ]);

                return $cachedForecast;
            }

            $csvData = $this->convertToCsv($maintenanceData);

            Log::info("Sending maintenance forecast request for year: {$year}");

            /** @var Response $response */
            $response = $this->postWithRetry('/api/forecast/maintenance', [
                'csv_data' => $csvData,
                'year' => $year,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $result = $this->attachActualsToMonthlyForecasts((array) $result, (int) $year);
                $this->cacheForecast($cacheKey, $result);
                Log::info('Maintenance forecast API response received', $result);

                return $result;
            } else {
                $error = $this->formatApiError($response->status(), $response->body());
                Log::error($error);
                throw new \Exception($error);
            }
        } catch (\Exception $e) {
            Log::error('Maintenance forecast service error: '.$e->getMessage());

            // Return a fallback response so the UI remains usable during API outages.
            $fallbackForecast = $this->buildFallbackForecast((int) $year, (array) $maintenanceData, $e->getMessage());
            $this->cacheForecast($this->buildForecastCacheKey((int) $year, $this->getTrainingDataSignature()), $fallbackForecast, true);

            return $fallbackForecast;
        }
    }

    private function buildForecastCacheKey(int $year, string $signature): string
    {
        return "maintenance_forecast:v1:year:{$year}:sig:{$signature}";
    }

    private function getTrainingDataSignature(): string
    {
        $summary = DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->where('mr.status', 'Completed')
            ->whereNotNull('ml.completion_date')
            ->whereNotNull('ml.cost')
            ->selectRaw('COUNT(*) as row_count, COALESCE(MAX(ml.updated_at), MAX(ml.created_at), MAX(mr.updated_at), MAX(mr.created_at)) as latest_change, COALESCE(SUM(ml.cost), 0) as total_cost, MIN(ml.completion_date) as min_date, MAX(ml.completion_date) as max_date')
            ->first();

        $rowCount = (int) ($summary->row_count ?? 0);
        $latestChange = (string) ($summary->latest_change ?? 'none');
        $totalCost = number_format((float) ($summary->total_cost ?? 0), 2, '.', '');
        $minDate = (string) ($summary->min_date ?? 'none');
        $maxDate = (string) ($summary->max_date ?? 'none');

        return sha1("{$rowCount}|{$latestChange}|{$totalCost}|{$minDate}|{$maxDate}");
    }

    private function cacheForecast(string $cacheKey, array $forecast, bool $isFallback = false): void
    {
        try {
            $ttlSeconds = $isFallback
                ? max(60, (int) floor($this->forecastCacheTtlSeconds / 4))
                : $this->forecastCacheTtlSeconds;

            Cache::put($cacheKey, $forecast, now()->addSeconds($ttlSeconds));
        } catch (Throwable $exception) {
            Log::warning('Failed to cache maintenance forecast result', [
                'cache_key' => $cacheKey,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function postWithRetry(string $endpoint, array $payload): Response
    {
        $url = rtrim($this->apiBaseUrl, '/').'/'.ltrim($endpoint, '/');
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                /** @var Response $response */
                $response = Http::timeout($this->timeoutSeconds)
                    ->asJson()
                    ->post($url, $payload);

                if ($response->successful()) {
                    return $response;
                }

                if (! $this->shouldRetryStatus($response->status()) || $attempt === $this->retryAttempts) {
                    return $response;
                }

                Log::warning('Transient maintenance forecast API response, retrying', [
                    'attempt' => $attempt,
                    'max_attempts' => $this->retryAttempts,
                    'status' => $response->status(),
                ]);

                $this->sleepWithBackoff($attempt);
            } catch (Throwable $exception) {
                $lastException = $exception;

                if (! $this->shouldRetryException($exception) || $attempt === $this->retryAttempts) {
                    throw $exception;
                }

                Log::warning('Transient maintenance forecast API exception, retrying', [
                    'attempt' => $attempt,
                    'max_attempts' => $this->retryAttempts,
                    'error' => $exception->getMessage(),
                ]);

                $this->sleepWithBackoff($attempt);
            }
        }

        if ($lastException instanceof Throwable) {
            throw $lastException;
        }

        throw new \RuntimeException('Unable to reach maintenance forecast API after retry attempts.');
    }

    private function shouldRetryStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function shouldRetryException(Throwable $exception): bool
    {
        return $exception instanceof ConnectionException || $exception instanceof RequestException;
    }

    private function sleepWithBackoff(int $attempt): void
    {
        $exponent = max(0, $attempt - 1);
        $delayMs = ($this->retryBaseDelayMs * (2 ** $exponent)) + random_int(0, 250);
        usleep($delayMs * 1000);
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
            $date = isset($row['date']) ? (string) $row['date'] : null;
            $cost = isset($row['cost']) ? (float) $row['cost'] : 0.0;
            $urgency = isset($row['urgency']) ? (string) $row['urgency'] : null;

            if (! $date) {
                continue;
            }

            $timestamp = strtotime($date);
            if ($timestamp === false) {
                continue;
            }

            $monthKey = date('Y-m', $timestamp);

            if (! isset($monthlyAgg[$monthKey])) {
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
                'maintenance_count_estimate' => (int) max(0, round($avgMonthlyJobs)),
                'urgency_estimate' => round($avgUrgency, 2),
                'seasonal_factor' => 1.0,
            ];
        }

        $fallback = [
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
            'data_points_used' => (int) count($maintenanceData),
            'model_performance' => [
                'r2_score' => 0,
                'mae' => 0,
                'mape' => 0,
                'clusters_used' => 0,
            ],
        ];

        return $this->attachActualsToMonthlyForecasts($fallback, $year);
    }

    private function attachActualsToMonthlyForecasts(array $result, int $year): array
    {
        if (! isset($result['monthly_forecasts']) || ! is_array($result['monthly_forecasts'])) {
            return $result;
        }

        $actualCosts = $this->getCurrentYearMonthlyActualCosts($year);
        $actualJobCounts = $this->getCurrentYearMonthlyActualJobCounts($year);

        foreach ($result['monthly_forecasts'] as &$monthForecast) {
            $monthNumber = isset($monthForecast['month']) ? (int) $monthForecast['month'] : 0;

            $monthForecast['actual_cost'] = round((float) ($actualCosts[$monthNumber] ?? 0), 2);
            $monthForecast['actual_job_count'] = (int) ($actualJobCounts[$monthNumber] ?? 0);
        }
        unset($monthForecast);

        return $result;
    }

    private function getCurrentYearMonthlyActualCosts(int $year): array
    {
        $monthExpr = $this->completionMonthExpression();

        return DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->where('mr.status', 'Completed')
            ->whereYear('ml.completion_date', $year)
            ->selectRaw("{$monthExpr} as month, SUM(ml.cost) as total_cost")
            ->groupBy('month')
            ->pluck('total_cost', 'month')
            ->map(fn ($total) => (float) $total)
            ->all();
    }

    private function getCurrentYearMonthlyActualJobCounts(int $year): array
    {
        $monthExpr = $this->completionMonthExpression();

        return DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->where('mr.status', 'Completed')
            ->whereYear('ml.completion_date', $year)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as total_jobs")
            ->groupBy('month')
            ->pluck('total_jobs', 'month')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    private function completionMonthExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return 'EXTRACT(MONTH FROM ml.completion_date)::int';
        }

        return 'MONTH(ml.completion_date)';
    }

    private function urgencyToScore(?string $urgency): float
    {
        $level = strtoupper((string) $urgency);

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
                    $row['urgency_score'] ?? '',
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
            'format' => isset($firstRow['monthly_maintenance_cost']) ? 'monthly' : 'individual',
        ]);

        return $csv;
    }

    public function getMaintenanceDataForForecast()
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'pgsql'
            ? 'EXTRACT(YEAR FROM ml.completion_date)::int'
            : 'YEAR(ml.completion_date)';
        $monthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM ml.completion_date)::int'
            : 'MONTH(ml.completion_date)';

        // Aggregate by month to reduce payload while preserving trend signal.
        $monthlyRows = DB::table('maintenance_requests as mr')
            ->join('maintenance_logs as ml', 'mr.request_id', '=', 'ml.request_id')
            ->where('mr.status', 'Completed')
            ->whereNotNull('ml.completion_date')
            ->whereNotNull('ml.cost')
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, SUM(ml.cost) as cost, COUNT(*) as item_count")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $year = (int) ($item->year ?? 0);
                $month = (int) ($item->month ?? 1);

                return [
                    'date' => sprintf('%04d-%02d-01', $year, $month),
                    'cost' => (float) ($item->cost ?? 0),
                    'urgency' => 'MEDIUM',
                    'category' => 'General',
                    'item_count' => (int) ($item->item_count ?? 0),
                ];
            })
            ->toArray();

        Log::info('Sending '.count($monthlyRows).' monthly aggregated maintenance records to API for training.');

        return $monthlyRows;
    }

    private function getMonthlyDateRange($monthlyData)
    {
        if (empty($monthlyData)) {
            return 'No data';
        }

        $first = $monthlyData[0];
        $last = $monthlyData[count($monthlyData) - 1];

        return $first['year'].'-'.$first['month'].' to '.$last['year'].'-'.$last['month'];
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
            'date_range' => ($stats->earliest_date ?? 'N/A').' to '.($stats->latest_date ?? 'N/A'),
            'total_cost' => $stats->total_cost ?? 0,
            'avg_monthly_cost' => $stats->avg_cost ?? 0,
        ];
    }

    private function inferCategoryFromProblem($requestId)
    {
        // Temporary solution - you should add a 'category' column to maintenance_request table
        $categories = ['Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control'];

        return $categories[array_rand($categories)];
    }
}
