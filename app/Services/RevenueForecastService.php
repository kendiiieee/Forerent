<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RevenueForecastService
{
    private $fastApiUrl;

    private int $timeoutSeconds;

    private int $retryAttempts;

    private int $retryBaseDelayMs;

    private int $forecastCacheTtlSeconds;

    public function __construct()
    {
        $this->fastApiUrl = $this->resolveApiBaseUrl();
        $this->timeoutSeconds = (int) env('FORECAST_API_TIMEOUT_SECONDS', 120);
        $this->retryAttempts = max(1, (int) env('FORECAST_API_RETRY_ATTEMPTS', 3));
        $this->retryBaseDelayMs = max(100, (int) env('FORECAST_API_RETRY_BASE_DELAY_MS', 400));
        $this->forecastCacheTtlSeconds = max(60, (int) env('FORECAST_CACHE_TTL_SECONDS', 900));
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

    public function generateMonthlyForecast($year = null)
    {
        if (! $year) {
            $year = Carbon::now()->year;
        }

        $year = (int) $year;

        $dataSignature = $this->getTrainingDataSignature();
        $cacheKey = $this->buildForecastCacheKey($year, $dataSignature);
        $cachedForecast = Cache::get($cacheKey);

        if (is_array($cachedForecast)) {
            Log::info('Revenue forecast cache hit', [
                'year' => $year,
                'cache_key' => $cacheKey,
            ]);

            return $cachedForecast;
        }

        Log::info("Starting revenue forecast generation for year: {$year}");

        $fallbackReason = 'Forecast service unavailable';

        try {
            // Export monthly aggregated inflow data to CSV string to reduce payload size.
            $csvData = $this->exportMonthlyInflowDataAsCsv();

            Log::info('Transaction data exported, CSV length: '.strlen($csvData));

            // Call FastAPI endpoint
            /** @var Response $response */
            $response = $this->postWithRetry('/api/forecast/revenue', [
                'csv_data' => $csvData,
                'year' => $year,
            ]);

            if (! $response->successful()) {
                $fallbackReason = $this->formatApiError($response->status(), $response->body());
                Log::error('FastAPI forecast request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $fallbackForecast = $this->buildFallbackForecast($year, $fallbackReason);
                $this->cacheForecast($cacheKey, $fallbackForecast, true);

                return $fallbackForecast;
            }

            $forecastData = $response->json();

            if (! isset($forecastData['success']) || ! $forecastData['success']) {
                $error = $forecastData['detail'] ?? $forecastData['error'] ?? 'Forecast failed';
                $fallbackForecast = $this->buildFallbackForecast($year, (string) $error);
                $this->cacheForecast($cacheKey, $fallbackForecast, true);

                return $fallbackForecast;
            }

            Log::info('Forecast generated successfully', [
                'forecast_year' => $forecastData['forecast_year'],
                'total_annual' => $forecastData['total_annual_revenue'],
                'data_points' => $forecastData['data_points_used'] ?? 0,
            ]);

            $this->cacheForecast($cacheKey, $forecastData);

            return $forecastData;
        } catch (\Exception $e) {
            Log::error('Revenue forecast generation failed', [
                'error' => $e->getMessage(),
                'year' => $year,
            ]);

            $fallbackReason = $e->getMessage() ?: $fallbackReason;
            $fallbackForecast = $this->buildFallbackForecast($year, $fallbackReason);
            $this->cacheForecast($cacheKey, $fallbackForecast, true);

            return $fallbackForecast;
        }
    }

    private function buildForecastCacheKey(int $year, string $signature): string
    {
        return "revenue_forecast:v1:year:{$year}:sig:{$signature}";
    }

    private function getTrainingDataSignature(): string
    {
        $summary = $this->baseCreditInflowQuery()
            ->selectRaw('COUNT(*) as row_count, COALESCE(MAX(updated_at), MAX(created_at)) as latest_change, COALESCE(SUM(amount), 0) as total_amount, MIN(transaction_date) as min_date, MAX(transaction_date) as max_date')
            ->first();

        $rowCount = (int) ($summary->row_count ?? 0);
        $latestChange = (string) ($summary->latest_change ?? 'none');
        $totalAmount = number_format((float) ($summary->total_amount ?? 0), 2, '.', '');
        $minDate = (string) ($summary->min_date ?? 'none');
        $maxDate = (string) ($summary->max_date ?? 'none');

        return sha1("{$rowCount}|{$latestChange}|{$totalAmount}|{$minDate}|{$maxDate}");
    }

    private function cacheForecast(string $cacheKey, array $forecast, bool $isFallback = false): void
    {
        try {
            $ttlSeconds = $isFallback
                ? max(60, (int) floor($this->forecastCacheTtlSeconds / 4))
                : $this->forecastCacheTtlSeconds;

            Cache::put($cacheKey, $forecast, now()->addSeconds($ttlSeconds));
        } catch (Throwable $exception) {
            Log::warning('Failed to cache revenue forecast result', [
                'cache_key' => $cacheKey,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function postWithRetry(string $endpoint, array $payload): Response
    {
        $url = rtrim($this->fastApiUrl, '/').'/'.ltrim($endpoint, '/');
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

                $this->sleepWithBackoff($attempt);
            } catch (Throwable $exception) {
                $lastException = $exception;

                if ($this->isTimeoutException($exception)) {
                    Log::warning('Revenue forecast API timeout encountered; skipping retries', [
                        'attempt' => $attempt,
                        'max_attempts' => $this->retryAttempts,
                        'timeout_seconds' => $this->timeoutSeconds,
                        'error' => $exception->getMessage(),
                    ]);

                    throw $exception;
                }

                if (! $this->shouldRetryException($exception) || $attempt === $this->retryAttempts) {
                    throw $exception;
                }

                Log::warning('Transient revenue forecast API exception, retrying', [
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

        throw new \RuntimeException('Unable to reach revenue forecast API after retry attempts.');
    }

    private function shouldRetryStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function shouldRetryException(Throwable $exception): bool
    {
        return $exception instanceof ConnectionException || $exception instanceof RequestException;
    }

    private function isTimeoutException(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'cURL error 28');
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
            return "Forecast service unavailable. Status: {$status}";
        }

        $short = mb_substr($normalized, 0, 220);

        return "Forecast service unavailable. Status: {$status}. {$short}";
    }

    private function buildFallbackForecast(int $year, string $reason): array
    {
        $currentYearTotals = $this->getCurrentYearMonthlyInflowTotals($year);
        $historicalAverages = $this->getHistoricalMonthlyAverages();

        $monthlyForecasts = [];
        $annual = 0.0;

        for ($month = 1; $month <= 12; $month++) {
            $forecastedRevenue = (float) ($historicalAverages[$month] ?? 0);
            $actualRevenue = (float) ($currentYearTotals[$month] ?? 0);

            $monthlyForecasts[] = [
                'year' => $year,
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
                'forecasted_revenue' => round($forecastedRevenue, 2),
                'actual_revenue' => round($actualRevenue, 2),
            ];

            $annual += $forecastedRevenue;
        }

        return [
            'success' => true,
            'is_fallback' => true,
            'warning' => 'Using fallback forecast generated from historical inflow data.',
            'error' => $reason,
            'forecast_year' => $year,
            'monthly_forecasts' => $monthlyForecasts,
            'total_annual_revenue' => round($annual, 2),
            'total_remaining_revenue' => round($annual, 2),
            'average_monthly_revenue' => round($annual / 12, 2),
            'data_points_used' => $this->baseCreditInflowQuery()->count(),
        ];
    }

    private function getCurrentYearMonthlyInflowTotals(int $year): array
    {
        $monthExpr = $this->monthExpression('transaction_date');

        $rows = $this->baseCreditInflowQuery()
            ->whereYear('transaction_date', $year)
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->get();

        $totals = [];
        foreach ($rows as $row) {
            $totals[(int) $row->month] = (float) $row->total;
        }

        return $totals;
    }

    private function getHistoricalMonthlyAverages(): array
    {
        $yearExpr = $this->yearExpression('transaction_date');
        $monthExpr = $this->monthExpression('transaction_date');

        $rows = $this->baseCreditInflowQuery()
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, SUM(amount) as total")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $buckets = [];
        foreach ($rows as $row) {
            $month = (int) $row->month;
            if (! isset($buckets[$month])) {
                $buckets[$month] = [];
            }

            $buckets[$month][] = (float) $row->total;
        }

        $averages = [];
        for ($month = 1; $month <= 12; $month++) {
            $values = $buckets[$month] ?? [];
            $averages[$month] = empty($values) ? 0.0 : (array_sum($values) / count($values));
        }

        return $averages;
    }

    private function exportMonthlyInflowDataAsCsv()
    {
        $yearExpr = $this->yearExpression('transaction_date');
        $monthExpr = $this->monthExpression('transaction_date');

        $monthlyRows = $this->baseCreditInflowQuery()
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, SUM(amount) as amount, COUNT(*) as transaction_count")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        Log::info('Exporting monthly inflow aggregates for forecasting', [
            'months' => $monthlyRows->count(),
        ]);

        $output = fopen('php://temp', 'r+');

        // Keep column names compatible with Python preprocessing logic.
        fputcsv($output, [
            'transaction_id',
            'transaction_type',
            'category',
            'transaction_date',
            'amount',
            'reference_number',
        ]);

        $rowId = 1;

        foreach ($monthlyRows as $row) {
            $monthStart = Carbon::createFromDate((int) $row->year, (int) $row->month, 1)->format('Y-m-d');

            fputcsv($output, [
                $rowId++,
                'CREDIT',
                'Revenue Aggregate',
                $monthStart,
                (float) $row->amount,
                null,
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }

    private function monthExpression(string $column): string
    {
        $driver = Transaction::query()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return "EXTRACT(MONTH FROM {$column})::int";
        }

        return "MONTH({$column})";
    }

    private function yearExpression(string $column): string
    {
        $driver = Transaction::query()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return "EXTRACT(YEAR FROM {$column})::int";
        }

        return "YEAR({$column})";
    }

    private function baseCreditInflowQuery(): Builder
    {
        return Transaction::query()->creditInflows();
    }
}
