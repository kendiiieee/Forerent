<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Carbon\Carbon;
use Throwable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class RevenueForecastService
{
    private $fastApiUrl;
    private int $timeoutSeconds;
    private int $retryAttempts;
    private int $retryBaseDelayMs;

    public function __construct()
    {
        $this->fastApiUrl = env('FASTAPI_URL', env('PYTHON_API_URL', 'http://localhost:8000'));
        $this->timeoutSeconds = (int) env('FORECAST_API_TIMEOUT_SECONDS', 120);
        $this->retryAttempts = max(1, (int) env('FORECAST_API_RETRY_ATTEMPTS', 3));
        $this->retryBaseDelayMs = max(100, (int) env('FORECAST_API_RETRY_BASE_DELAY_MS', 400));
    }

    public function generateMonthlyForecast($year = null)
    {
        if (!$year) {
            $year = Carbon::now()->year;
        }

        Log::info("Starting revenue forecast generation for year: {$year}");

        $fallbackReason = 'Forecast service unavailable';

        try {
            // Export transaction data to CSV string
            $csvData = $this->exportTransactionDataAsCsv();

            Log::info("Transaction data exported, CSV length: " . strlen($csvData));

            // Call FastAPI endpoint
            /** @var Response $response */
            $response = $this->postWithRetry('/api/forecast/revenue', [
                    'csv_data' => $csvData,
                    'year' => $year
                ]);

            if (!$response->successful()) {
                $fallbackReason = $this->formatApiError($response->status(), $response->body());
                Log::error('FastAPI forecast request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->buildFallbackForecast((int) $year, $fallbackReason);
            }

            $forecastData = $response->json();

            if (!isset($forecastData['success']) || !$forecastData['success']) {
                $error = $forecastData['detail'] ?? $forecastData['error'] ?? 'Forecast failed';
                return $this->buildFallbackForecast((int) $year, (string) $error);
            }

            Log::info("Forecast generated successfully", [
                'forecast_year' => $forecastData['forecast_year'],
                'total_annual' => $forecastData['total_annual_revenue'],
                'data_points' => $forecastData['data_points_used'] ?? 0
            ]);

            return $forecastData;
        } catch (\Exception $e) {
            Log::error('Revenue forecast generation failed', [
                'error' => $e->getMessage(),
                'year' => $year
            ]);

            $fallbackReason = $e->getMessage() ?: $fallbackReason;

            return $this->buildFallbackForecast((int) $year, $fallbackReason);
        }
    }

    private function postWithRetry(string $endpoint, array $payload): Response
    {
        $url = rtrim($this->fastApiUrl, '/') . '/' . ltrim($endpoint, '/');
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

                if (!$this->shouldRetryStatus($response->status()) || $attempt === $this->retryAttempts) {
                    return $response;
                }

                $this->sleepWithBackoff($attempt);
            } catch (Throwable $exception) {
                $lastException = $exception;

                if (!$this->shouldRetryException($exception) || $attempt === $this->retryAttempts) {
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
        $currentYearTotals = $this->getCurrentYearMonthlyRentTotals($year);
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
            'warning' => 'Using fallback forecast generated from rent payment history.',
            'error' => $reason,
            'forecast_year' => $year,
            'monthly_forecasts' => $monthlyForecasts,
            'total_annual_revenue' => round($annual, 2),
            'total_remaining_revenue' => round($annual, 2),
            'average_monthly_revenue' => round($annual / 12, 2),
            'data_points_used' => Transaction::whereRaw('UPPER(transaction_type) = ?', ['CREDIT'])
                ->where('category', 'Rent Payment')
                ->count(),
        ];
    }

    private function getCurrentYearMonthlyRentTotals(int $year): array
    {
        $rows = Transaction::whereRaw('UPPER(transaction_type) = ?', ['CREDIT'])
            ->where('category', 'Rent Payment')
            ->whereYear('transaction_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM transaction_date)::int as month, SUM(amount) as total')
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
        $rows = Transaction::whereRaw('UPPER(transaction_type) = ?', ['CREDIT'])
            ->where('category', 'Rent Payment')
            ->selectRaw('EXTRACT(YEAR FROM transaction_date)::int as year, EXTRACT(MONTH FROM transaction_date)::int as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $buckets = [];
        foreach ($rows as $row) {
            $month = (int) $row->month;
            if (!isset($buckets[$month])) {
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

    private function exportTransactionDataAsCsv()
    {
        $transactions = Transaction::select([
            'transaction_id',
            'transaction_type',
            'category',
            'transaction_date',
            'amount',
            'reference_number'
        ])
            ->whereRaw('UPPER(transaction_type) = ?', ['CREDIT'])
            ->where('category', 'Rent Payment')
            ->orderBy('transaction_date')
            ->get();

        Log::info("Exporting {$transactions->count()} inflow transactions for forecasting");

        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, [
            'transaction_id',
            'transaction_type',
            'category',
            'transaction_date',
            'amount',
            'reference_number'
        ]);

        // Write data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction->transaction_id,
                $transaction->transaction_type,
                $transaction->category,
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->amount,
                $transaction->reference_number
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }
}
