<?php

namespace App\Livewire\Layouts;

use App\Models\Lease;
use App\Models\MaintenanceLog;
use App\Models\Transaction;
use App\Services\RevenueForecastService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class RevenueForecast extends Component
{
    public int $forecastYear;

    public array $monthlyForecasts = [];

    public array $monthlyExpenses = [];

    public array $revenueBreakdown = [];

    public float $totalAnnualRevenue = 0.0;

    public float $totalRemainingRevenue = 0.0;

    public float $averageMonthlyRevenue = 0.0;

    public bool $loading = false;

    public ?string $error = null;

    public ?string $warning = null;

    public bool $isFallback = false;

    public int $dataPointsUsed = 0;

    public bool $forecastLoaded = false;

    public array $insights = [];

    public float $activeLeaseDepositsTotal = 0.0;

    protected RevenueForecastService $revenueForecastService;

    public function boot(RevenueForecastService $revenueForecastService)
    {
        $this->revenueForecastService = $revenueForecastService;
    }

    public function mount()
    {
        $this->forecastYear = Carbon::now()->year;
    }

    public function loadForecast()
    {
        if ($this->forecastLoaded) {
            return;
        }

        $this->forecastLoaded = true;
        $this->generateForecast();
    }

    #[On('updateYear')]
    public function updateYear(int $year): void
    {
        $this->forecastYear = $year;

        if (! $this->forecastLoaded) {
            $this->forecastLoaded = true;
        }

        $this->generateForecast();
    }

    public function generateForecast()
    {
        $this->loading = true;
        $this->error = null;
        $this->warning = null;
        $this->isFallback = false;
        $this->monthlyForecasts = [];
        $this->totalAnnualRevenue = 0;
        $this->totalRemainingRevenue = 0;
        $this->averageMonthlyRevenue = 0;
        $this->dataPointsUsed = 0;
        $this->insights = [];

        try {
            $result = $this->revenueForecastService->generateMonthlyForecast($this->forecastYear);

            $this->monthlyForecasts = $result['monthly_forecasts'];
            $this->totalAnnualRevenue = $result['total_annual_revenue'];
            $this->totalRemainingRevenue = $result['total_remaining_revenue'];
            $this->averageMonthlyRevenue = $result['average_monthly_revenue'];
            $this->dataPointsUsed = $result['data_points_used'] ?? 0;
            $this->isFallback = (bool) ($result['is_fallback'] ?? false);
            $this->warning = $result['warning'] ?? null;

            // Add actual earnings data to each month
            if (! $this->isFallback) {
                $this->monthlyForecasts = $this->enrichForecastWithActualEarnings($this->monthlyForecasts);
            }

            $this->monthlyForecasts = $this->addPreviousYearRevenue($this->monthlyForecasts);
            // Compute monthly expenses and a revenue breakdown for the selected year
            $this->monthlyExpenses = $this->getMonthlyExpensesForYear((int) $this->forecastYear);
            $this->revenueBreakdown = $this->getRevenueBreakdownForYear((int) $this->forecastYear);
            $this->activeLeaseDepositsTotal = $this->getActiveLeaseDepositsTotal();
            $this->insights = $this->buildInsights($this->monthlyForecasts);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
            $this->dispatch('revenue-forecast-updated');
        }
    }

    private function enrichForecastWithActualEarnings(array $forecasts): array
    {
        if (empty($forecasts)) {
            return $forecasts;
        }

        try {
            $monthExpr = $this->transactionMonthExpression();
            $actualByMonth = Transaction::query()
                ->creditInflows()
                ->whereRaw('LOWER(COALESCE(category, \'\')) NOT LIKE ?', ['%deposit%'])
                ->whereYear('transaction_date', $this->forecastYear)
                ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
                ->groupBy('month')
                ->pluck('total', 'month');
        } catch (\Throwable $exception) {
            Log::warning('Revenue forecast actual earnings query failed; using zeros.', [
                'year' => $this->forecastYear,
                'error' => $exception->getMessage(),
            ]);

            $actualByMonth = collect();
        }

        foreach ($forecasts as &$monthForecast) {
            $monthNumber = $monthForecast['month'] ?? null;

            if ($monthNumber) {
                $monthForecast['actual_revenue'] = (float) ($actualByMonth[$monthNumber] ?? 0);
            } else {
                $monthForecast['actual_revenue'] = 0;
            }
        }

        return $forecasts;
    }

    private function addPreviousYearRevenue(array $forecasts): array
    {
        if (empty($forecasts)) {
            return $forecasts;
        }

        $previousYear = (int) $this->forecastYear - 1;
        $previousYearTotals = $this->getMonthlyTotalsForYear($previousYear);

        foreach ($forecasts as &$monthForecast) {
            $monthNumber = $monthForecast['month'] ?? null;

            if ($monthNumber) {
                $monthForecast['previous_year_revenue'] = (float) ($previousYearTotals[$monthNumber] ?? 0);
            } else {
                $monthForecast['previous_year_revenue'] = 0;
            }
        }

        return $forecasts;
    }

    private function getMonthlyTotalsForYear(int $year): array
    {
        try {
            $monthExpr = $this->transactionMonthExpression();

            $rows = Transaction::query()
                ->creditInflows()
                ->whereRaw('LOWER(COALESCE(category, \'\')) NOT LIKE ?', ['%deposit%'])
                ->whereYear('transaction_date', $year)
                ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
                ->groupBy('month')
                ->pluck('total', 'month');
        } catch (\Throwable $exception) {
            Log::warning('Revenue forecast monthly totals query failed; using empty totals.', [
                'year' => $year,
                'error' => $exception->getMessage(),
            ]);

            $rows = collect();
        }

        $totals = [];
        foreach ($rows as $month => $total) {
            $totals[(int) $month] = (float) $total;
        }

        return $totals;
    }

    private function buildInsights(array $forecasts): array
    {
        if (empty($forecasts)) {
            return [];
        }

        $now = Carbon::now();
        $forecastYear = (int) $this->forecastYear;
        $isCurrentYear = $forecastYear === (int) $now->year;
        $comparisonMonths = $isCurrentYear ? (int) $now->month : 12;
        $useForecastAsCurrent = $forecastYear > (int) $now->year;
        $periodLabel = $isCurrentYear ? 'YTD' : 'Full Year';

        $actualTotal = $this->sumMonthly($forecasts, 'actual_revenue', $comparisonMonths);
        $forecastBaseline = $this->sumMonthly($forecasts, 'forecasted_revenue', $comparisonMonths);
        $previousTotal = $this->sumMonthly($forecasts, 'previous_year_revenue', $comparisonMonths);
        $currentTotal = $useForecastAsCurrent ? $forecastBaseline : $actualTotal;

        $annualForecast = $this->sumMonthly($forecasts, 'forecasted_revenue', 12);
        $previousYearFull = $this->sumMonthly($forecasts, 'previous_year_revenue', 12);

        $peak = $this->getPeakForecastMonth($forecasts);
        $peakShare = $annualForecast > 0 ? ($peak['value'] / $annualForecast) * 100 : null;

        $baselineDelta = $this->calculatePercentDelta($currentTotal, $forecastBaseline);
        $previousDelta = $this->calculatePercentDelta($currentTotal, $previousTotal);
        $yearEndDelta = $this->calculatePercentDelta($annualForecast, $previousYearFull);

        return [
            [
                'label' => "Vs Forecast Baseline ({$periodLabel})",
                'value_text' => $this->formatSignedPercent($baselineDelta),
                'detail' => $this->formatCurrency($currentTotal).' vs '.$this->formatCurrency($forecastBaseline),
                'tone' => $this->toneFromDelta($baselineDelta),
            ],
            [
                'label' => "Vs Previous Year ({$periodLabel})",
                'value_text' => $this->formatSignedPercent($previousDelta),
                'detail' => $this->formatCurrency($currentTotal).' vs '.$this->formatCurrency($previousTotal),
                'tone' => $this->toneFromDelta($previousDelta),
            ],
            [
                'label' => 'Year-End Forecast vs Last Year',
                'value_text' => $this->formatSignedPercent($yearEndDelta),
                'detail' => $this->formatCurrency($annualForecast).' vs '.$this->formatCurrency($previousYearFull),
                'tone' => $this->toneFromDelta($yearEndDelta),
            ],
            [
                'label' => 'Peak Month Share',
                'value_text' => $peakShare === null ? 'N/A' : number_format($peakShare, 1).'%',
                'detail' => $peak['month_name'] ? $peak['month_name'].' forecast' : 'No data',
                'tone' => 'neutral',
            ],
        ];
    }

    private function sumMonthly(array $forecasts, string $key, int $maxMonth): float
    {
        $total = 0.0;

        foreach ($forecasts as $monthForecast) {
            $month = (int) ($monthForecast['month'] ?? 0);
            if ($month === 0 || $month > $maxMonth) {
                continue;
            }

            $total += (float) ($monthForecast[$key] ?? 0);
        }

        return $total;
    }

    private function getPeakForecastMonth(array $forecasts): array
    {
        $peakValue = null;
        $peakMonthName = null;

        foreach ($forecasts as $monthForecast) {
            $value = (float) ($monthForecast['forecasted_revenue'] ?? 0);
            if ($peakValue === null || $value > $peakValue) {
                $peakValue = $value;
                $peakMonthName = $monthForecast['month_name'] ?? null;
            }
        }

        return [
            'value' => $peakValue ?? 0.0,
            'month_name' => $peakMonthName,
        ];
    }

    private function calculatePercentDelta(float $current, float $baseline): ?float
    {
        if ($baseline <= 0) {
            return null;
        }

        return (($current - $baseline) / $baseline) * 100;
    }

    private function formatSignedPercent(?float $value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        $sign = $value > 0 ? '+' : '';

        return $sign.number_format($value, 1).'%';
    }

    private function formatCurrency(float $value): string
    {
        return '₱'.number_format($value, 0);
    }

    private function toneFromDelta(?float $delta): string
    {
        if ($delta === null) {
            return 'neutral';
        }

        return $delta >= 0 ? 'positive' : 'negative';
    }

    private function transactionMonthExpression(string $column = 'transaction_date'): string
    {
        $driver = Transaction::query()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return "EXTRACT(MONTH FROM {$column})::int";
        }

        return "MONTH({$column})";
    }

    private function getMonthlyExpensesForYear(int $year): array
    {
        try {
            $monthExpr = $this->transactionMonthExpression('transaction_date');

            $rows = Transaction::query()
                ->whereRaw('UPPER(COALESCE(transaction_type, \'\')) = ?', ['DEBIT'])
                ->whereYear('transaction_date', $year)
                ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
                ->groupBy('month')
                ->pluck('total', 'month');
        } catch (\Throwable $exception) {
            Log::warning('Revenue forecast expense query failed; using empty expenses.', [
                'year' => $year,
                'error' => $exception->getMessage(),
            ]);

            $rows = collect();
        }

        $totals = [];
        foreach ($rows as $month => $total) {
            $totals[(int) $month] = (float) $total;
        }

        // Also include maintenance costs recorded in MaintenanceLog (if any)
        try {
            $maintenanceMonthExpr = $this->transactionMonthExpression('completion_date');

            $maintenanceRows = MaintenanceLog::query()
                ->whereYear('completion_date', $year)
                ->selectRaw("{$maintenanceMonthExpr} as month, SUM(cost) as total")
                ->groupBy('month')
                ->pluck('total', 'month');

            foreach ($maintenanceRows as $month => $mTotal) {
                $m = (int) $month;
                $totals[$m] = ($totals[$m] ?? 0.0) + (float) $mTotal;
            }
        } catch (\Exception $e) {
            // If MaintenanceLog or its fields aren't present, silently ignore so chart still renders
        }

        return $totals;
    }

    private function getRevenueBreakdownForYear(int $year): array
    {
        $monthExpr = $this->transactionMonthExpression();
        $driver = Transaction::query()->getConnection()->getDriverName();
        $advanceMatch = $driver === 'pgsql'
            ? "COALESCE(reference_number, '') ILIKE 'ADV%'"
            : "LOWER(COALESCE(reference_number, '')) LIKE 'adv%'";
        $categoryExpr = "CASE WHEN {$advanceMatch} THEN 'Advance Payment' ELSE COALESCE(NULLIF(category, ''), 'Uncategorized') END";

        try {
            $rows = Transaction::query()
                ->creditInflows()
                ->whereRaw("LOWER({$categoryExpr}) NOT LIKE ?", ['%deposit%'])
                ->whereYear('transaction_date', $year)
                ->selectRaw("{$monthExpr} as month, {$categoryExpr} as category, SUM(amount) as total")
                ->groupByRaw("{$monthExpr}, {$categoryExpr}")
                ->orderByRaw($monthExpr)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Revenue forecast breakdown query failed; using empty breakdown.', [
                'year' => $year,
                'error' => $exception->getMessage(),
            ]);

            $rows = collect();
        }

        // Build a month-indexed table-like structure: [ monthNumber => [ 'month' => int, 'categories' => [category => amount] ] ]
        $table = [];
        // Initialize months 1..12
        for ($m = 1; $m <= 12; $m++) {
            $table[$m] = [
                'month' => $m,
                'categories' => [],
            ];
        }

        foreach ($rows as $row) {
            $month = (int) ($row->month ?? 0) ?: 0;
            if ($month < 1 || $month > 12) {
                continue;
            }
            $cat = (string) $row->category;
            $amt = (float) $row->total;
            $table[$month]['categories'][$cat] = ($table[$month]['categories'][$cat] ?? 0.0) + $amt;
        }

        // Convert to indexed array ordered by month for easier JSON consumption in the frontend
        return array_values($table);
    }

    private function getActiveLeaseDepositsTotal(): float
    {
        try {
            $total = Lease::query()
                ->where('status', 'Active')
                ->sum('security_deposit');

            return (float) $total;
        } catch (\Throwable $exception) {
            Log::warning('Revenue forecast active lease deposits query failed; using zero.', [
                'error' => $exception->getMessage(),
            ]);

            return 0.0;
        }
    }

    public function render()
    {
        return view('livewire.layouts.revenue-forecast');
    }
}
