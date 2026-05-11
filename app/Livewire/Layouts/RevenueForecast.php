<?php

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\RevenueForecastService;
use App\Models\Transaction;
use Carbon\Carbon;

class RevenueForecast extends Component
{
    public $forecastYear;
    public $monthlyForecasts = [];
    public $monthlyExpenses = [];
    public $revenueBreakdown = [];
    public $totalAnnualRevenue = 0;
    public $totalRemainingRevenue = 0;
    public $averageMonthlyRevenue = 0;
    public $loading = false;
    public $error = null;
    public $warning = null;
    public $isFallback = false;
    public $dataPointsUsed = 0;
    public $forecastLoaded = false;
    public $insights = [];

    protected $revenueForecastService;

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
    public function updateYear($year)
    {
        $this->forecastYear = $year;

        if (!$this->forecastLoaded) {
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
            $this->isFallback = (bool)($result['is_fallback'] ?? false);
            $this->warning = $result['warning'] ?? null;

            // Add actual earnings data to each month
            if (!$this->isFallback) {
                $this->monthlyForecasts = $this->enrichForecastWithActualEarnings($this->monthlyForecasts);
            }

            $this->monthlyForecasts = $this->addPreviousYearRevenue($this->monthlyForecasts);
            // Compute monthly expenses and a revenue breakdown for the selected year
            $this->monthlyExpenses = $this->getMonthlyExpensesForYear((int) $this->forecastYear);
            $this->revenueBreakdown = $this->getRevenueBreakdownForYear((int) $this->forecastYear);
            $this->insights = $this->buildInsights($this->monthlyForecasts);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
            $this->dispatch('revenue-forecast-updated');
        }
    }

    private function enrichForecastWithActualEarnings($forecasts)
    {
        if (empty($forecasts)) {
            return $forecasts;
        }

        $monthExpr = $this->transactionMonthExpression();
        $actualByMonth = Transaction::query()
            ->creditInflows()
            ->whereRaw('LOWER(COALESCE(category, \'\')) NOT LIKE ?', ['%deposit%'])
            ->whereYear('transaction_date', $this->forecastYear)
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

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
        $monthExpr = $this->transactionMonthExpression();

        $rows = Transaction::query()
            ->creditInflows()
            ->whereRaw('LOWER(COALESCE(category, \'\')) NOT LIKE ?', ['%deposit%'])
            ->whereYear('transaction_date', $year)
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

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
                'detail' => $this->formatCurrency($currentTotal) . ' vs ' . $this->formatCurrency($forecastBaseline),
                'tone' => $this->toneFromDelta($baselineDelta),
            ],
            [
                'label' => "Vs Previous Year ({$periodLabel})",
                'value_text' => $this->formatSignedPercent($previousDelta),
                'detail' => $this->formatCurrency($currentTotal) . ' vs ' . $this->formatCurrency($previousTotal),
                'tone' => $this->toneFromDelta($previousDelta),
            ],
            [
                'label' => 'Year-End Forecast vs Last Year',
                'value_text' => $this->formatSignedPercent($yearEndDelta),
                'detail' => $this->formatCurrency($annualForecast) . ' vs ' . $this->formatCurrency($previousYearFull),
                'tone' => $this->toneFromDelta($yearEndDelta),
            ],
            [
                'label' => 'Peak Month Share',
                'value_text' => $peakShare === null ? 'N/A' : number_format($peakShare, 1) . '%',
                'detail' => $peak['month_name'] ? $peak['month_name'] . ' forecast' : 'No data',
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

        return $sign . number_format($value, 1) . '%';
    }

    private function formatCurrency(float $value): string
    {
        return '₱' . number_format($value, 0);
    }

    private function toneFromDelta(?float $delta): string
    {
        if ($delta === null) {
            return 'neutral';
        }

        return $delta >= 0 ? 'positive' : 'negative';
    }

    private function transactionMonthExpression(): string
    {
        $driver = Transaction::query()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return 'EXTRACT(MONTH FROM transaction_date)::int';
        }

        return 'MONTH(transaction_date)';
    }

    private function getMonthlyExpensesForYear(int $year): array
    {
        $monthExpr = $this->transactionMonthExpression();

        $rows = Transaction::query()
            ->whereRaw('UPPER(COALESCE(transaction_type, \'\')) = ?', ['DEBIT'])
            ->whereYear('transaction_date', $year)
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $totals = [];
        foreach ($rows as $month => $total) {
            $totals[(int) $month] = (float) $total;
        }

        return $totals;
    }

    private function getRevenueBreakdownForYear(int $year): array
    {
        $rows = Transaction::query()
            ->creditInflows()
            ->whereRaw('LOWER(COALESCE(category, \'\')) NOT LIKE ?', ['%deposit%'])
            ->whereYear('transaction_date', $year)
            ->selectRaw('COALESCE(category, \'Uncategorized\') as category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $breakdown = [];
        foreach ($rows as $row) {
            $breakdown[] = [
                'category' => (string) $row->category,
                'amount' => (float) $row->total,
            ];
        }

        return $breakdown;
    }

    public function render()
    {
        return view('livewire.layouts.revenue-forecast');
    }
}
