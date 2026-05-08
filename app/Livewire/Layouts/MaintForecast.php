<?php
// app/Livewire/Layouts/MaintForecast.php

namespace App\Livewire\Layouts;

use Livewire\Component;
use App\Services\MaintenanceForecast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaintForecast extends Component
{
    public $year;
    public $forecast = null;
    public $maintenanceStats = null;
    public $isGenerating = false;
    public $error = null;
    public $hasData = false;
    public $debugInfo = null;
    public $forecastLoaded = false;
    public $insights = [];

    protected $rules = [
        'year' => 'required|integer|min:2023|max:2030'
    ];

    public function mount()
    {
        $this->year = date('Y');
        $this->loadStats();
    }

    public function loadForecast()
    {
        if ($this->forecastLoaded) {
            return;
        }

        $this->forecastLoaded = true;
        $this->generateForecast();
    }

    public function updateYear($year)
    {
        $this->year = (int) $year;

        if (!$this->forecastLoaded) {
            $this->forecastLoaded = true;
        }

        $this->generateForecast();
    }

    public function loadStats()
    {
        try {
            $service = app(MaintenanceForecast::class);
            $stats = $service->getMaintenanceStats();

            $this->maintenanceStats = [
                'total_records' => $stats['total_records'] ?? 0,
                'date_range' => $stats['date_range'] ?? 'No data available',
                'total_cost' => $stats['total_cost'] ?? 0,
                'avg_monthly_cost' => $stats['avg_monthly_cost'] ?? 0
            ];

            $this->hasData = ($this->maintenanceStats['total_records'] ?? 0) > 0;
        } catch (\Exception $e) {
            $this->maintenanceStats = [
                'total_records' => 0,
                'date_range' => 'Error',
                'total_cost' => 0,
                'avg_monthly_cost' => 0
            ];
            $this->hasData = false;
            $this->error = 'Failed to load maintenance stats: ' . $e->getMessage();
        }
    }

    public function generateForecast()
    {
        Log::info('=== FORECAST GENERATION STARTED ===');
        $this->validate();
        $this->isGenerating = true;
        $this->error = null;
        $this->forecast = null;
        $this->debugInfo = null;
        $this->insights = [];

        try {
            $service = app(MaintenanceForecast::class);
            
            // 1. Get the RAW data for the API
            $maintenanceData = $service->getMaintenanceDataForForecast();
            
            if (empty($maintenanceData)) {
                throw new \Exception('No maintenance data found to send to the API.');
            }

            Log::info('Raw data for API', ['record_count' => count($maintenanceData)]);

            // 2. Call the API with the raw data
            $this->forecast = $service->generateForecast($this->year, $maintenanceData);

            // 3. Process the response
            if (!is_array($this->forecast)) {
                throw new \Exception('Invalid forecast response format: ' . gettype($this->forecast));
            }

            if (isset($this->forecast['success']) && $this->forecast['success'] === false) {
                $this->debugInfo = $this->forecast['debug_info'] ?? null;
                throw new \Exception($this->forecast['error'] ?? 'Forecast generation failed');
            }

            if (!isset($this->forecast['monthly_forecasts']) || empty($this->forecast['monthly_forecasts'])) {
                throw new \Exception('Forecast response missing monthly_forecasts');
            }

            $this->forecast['monthly_forecasts'] = $this->addPreviousYearCosts($this->forecast['monthly_forecasts']);
            $this->insights = $this->buildInsights($this->forecast['monthly_forecasts']);

            Log::info('✅ FORECAST GENERATED SUCCESSFULLY');

        } catch (\Exception $e) {
            Log::error('❌ Forecast generation failed', [
                'error' => $e->getMessage(),
            ]);
            $this->error = 'Failed to generate forecast: ' . $e->getMessage();
            $this->forecast = null;
        } finally {
            $this->isGenerating = false;
            $this->dispatch('maintenance-forecast-updated');
        }

        Log::info('=== FORECAST GENERATION COMPLETED ===');
    }

    public function render()
    {
        // This line tells Laravel to load the file at:
        // resources/views/livewire/layouts/maint-forecast.blade.php
        return view('livewire.layouts.maint-forecast');
    }

    private function addPreviousYearCosts(array $forecasts): array
    {
        if (empty($forecasts)) {
            return $forecasts;
        }

        $previousYear = (int) $this->year - 1;
        $previousYearTotals = $this->getMonthlyActualCostsForYear($previousYear);

        foreach ($forecasts as &$monthForecast) {
            $monthNumber = $monthForecast['month'] ?? null;

            if ($monthNumber) {
                $monthForecast['previous_year_cost'] = (float) ($previousYearTotals[$monthNumber] ?? 0);
            } else {
                $monthForecast['previous_year_cost'] = 0;
            }
        }

        return $forecasts;
    }

    private function getMonthlyActualCostsForYear(int $year): array
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

    private function completionMonthExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return 'EXTRACT(MONTH FROM ml.completion_date)::int';
        }

        return 'MONTH(ml.completion_date)';
    }

    private function buildInsights(array $forecasts): array
    {
        if (empty($forecasts)) {
            return [];
        }

        $now = Carbon::now();
        $forecastYear = (int) $this->year;
        $isCurrentYear = $forecastYear === (int) $now->year;
        $comparisonMonths = $isCurrentYear ? (int) $now->month : 12;
        $useForecastAsCurrent = $forecastYear > (int) $now->year;
        $periodLabel = $isCurrentYear ? 'YTD' : 'Full Year';

        $actualTotal = $this->sumMonthly($forecasts, 'actual_cost', $comparisonMonths);
        $forecastBaseline = $this->sumMonthly($forecasts, 'forecasted_cost', $comparisonMonths);
        $previousTotal = $this->sumMonthly($forecasts, 'previous_year_cost', $comparisonMonths);
        $currentTotal = $useForecastAsCurrent ? $forecastBaseline : $actualTotal;

        $annualForecast = $this->sumMonthly($forecasts, 'forecasted_cost', 12);
        $previousYearFull = $this->sumMonthly($forecasts, 'previous_year_cost', 12);

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
                'tone' => $this->toneFromDelta($baselineDelta, true),
            ],
            [
                'label' => "Vs Previous Year ({$periodLabel})",
                'value_text' => $this->formatSignedPercent($previousDelta),
                'detail' => $this->formatCurrency($currentTotal) . ' vs ' . $this->formatCurrency($previousTotal),
                'tone' => $this->toneFromDelta($previousDelta, true),
            ],
            [
                'label' => 'Year-End Forecast vs Last Year',
                'value_text' => $this->formatSignedPercent($yearEndDelta),
                'detail' => $this->formatCurrency($annualForecast) . ' vs ' . $this->formatCurrency($previousYearFull),
                'tone' => $this->toneFromDelta($yearEndDelta, true),
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
            $value = (float) ($monthForecast['forecasted_cost'] ?? 0);
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

    private function toneFromDelta(?float $delta, bool $invert = false): string
    {
        if ($delta === null) {
            return 'neutral';
        }

        if ($invert) {
            return $delta <= 0 ? 'positive' : 'negative';
        }

        return $delta >= 0 ? 'positive' : 'negative';
    }
}