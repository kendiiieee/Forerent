<div class="bg-white rounded-lg shadow-md p-6" wire:init="loadForecast">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[#070642]">Maintenance Cost Forecast</h2>
    </div>

    @if($error)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if($forecast && ($forecast['is_fallback'] ?? false))
        <div class="bg-amber-100 border border-amber-300 text-amber-900 px-4 py-3 rounded-xl mb-4">
            <strong>Notice:</strong> {{ $forecast['warning'] ?? 'Using fallback forecast data.' }}
        </div>
    @endif

    <script type="application/json" id="maintenanceForecastPayload">{!! json_encode([
        'forecastYear' => $year,
        'monthlyForecasts' => $forecast['monthly_forecasts'] ?? [],
    ]) !!}</script>

    @if(!$forecastLoaded || $isGenerating)
        <div class="text-center py-16 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-700">Loading Forecast</h3>
            <p class="mt-1 text-sm text-gray-500">Generating maintenance predictions...</p>
        </div>
    @elseif($forecast && isset($forecast['success']) && $forecast['success'])
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-[#070642]">Monthly Maintenance Cost Forecast - {{ $year }}</h3>
                </div>

                <div class="flex items-center gap-6">
                    <div x-data="{ open: false }" @click.away="open = false" @keydown.escape.stop="open = false" class="relative">
                        <button
                            @click="open = !open"
                            type="button"
                            class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-[#070642] shadow-sm hover:border-gray-300 transition-all focus:outline-none focus:ring-0"
                        >
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download</span>
                            <svg :class="{ 'rotate-180': open }" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition.origin.top.right
                            style="display: none;"
                            class="absolute right-0 z-30 w-40 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
                        >
                            <button type="button" @click="downloadMaintenanceChart('svg'); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download SVG
                            </button>
                            <button type="button" @click="downloadMaintenanceChart('png'); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download PNG
                            </button>
                            <button type="button" @click="downloadMaintenanceCsv(); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Overview notice --}}
            @php
                $prevInsight = collect($insights ?? [])->first(fn($i) => str_contains($i['label'] ?? '', 'Previous Year'));
                $baselineInsight = collect($insights ?? [])->first(fn($i) => str_contains($i['label'] ?? '', 'Forecast Baseline'));
                $periodLabel = $year === now()->year ? 'year-to-date (YTD)' : 'full-year';
                $trendDirection = ($prevInsight['tone'] ?? 'neutral') === 'positive'
                    ? 'up'
                    : (($prevInsight['tone'] ?? 'neutral') === 'negative' ? 'down' : 'flat');
                $trendValue = $prevInsight['value_text'] ?? 'N/A';
                $baselineValue = $baselineInsight['value_text'] ?? 'N/A';
            @endphp
            <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-900">
                <span class="font-semibold">Overview:</span>
                This chart compares actual maintenance spending with the forecast, using last year as a reference point. For {{ $periodLabel }}, costs are {{ $trendDirection }} {{ $trendValue }} versus last year. The baseline comparison ({{ $baselineValue }}) shows whether spending is above or below the plan.
            </div>

            <div class="flex items-center gap-5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #8CC5FF;"></span>
                    <span class="text-sm text-gray-500 font-medium">Actual Cost</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #1E1B4B;"></span>
                    <span class="text-sm text-gray-500 font-medium">Forecasted Cost</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #F59E0B;"></span>
                    <span class="text-sm text-gray-500 font-medium">Previous Year</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <div class="lg:col-span-2">
                    <div class="relative flex-1" style="height: 430px;" wire:ignore>
                        <canvas id="maintenanceChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-1 rounded-xl border border-gray-100 p-4 flex flex-col" wire:ignore>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-1">
                                <h4 class="text-lg font-bold text-[#070642]">Job Count Forecast</h4>
                                <flux:tooltip :content="'Monthly forecasted vs actual maintenance jobs.'" position="top">
                                    <svg class="h-3.5 w-3.5 text-gray-400 hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9 9.75a.75.75 0 011.5 0v4a.75.75 0 01-1.5 0v-4z" clip-rule="evenodd" />
                                    </svg>
                                </flux:tooltip>
                            </div>
                            <p class="text-sm text-gray-500">Forecast vs actual jobs per month</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm" style="background-color: #1E1B4B;"></span>
                            <span class="text-xs text-gray-500 font-medium">Forecasted</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm" style="background-color: #8CC5FF;"></span>
                            <span class="text-xs text-gray-500 font-medium">Actual</span>
                        </div>
                    </div>
                    <div id="jobCountChart" class="min-h-64 flex-1" style="height: 320px;"></div>
                </div>
            </div>

            @if(!empty($insights))
                <div class="mt-6 border-t border-gray-100 pt-5">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.15em]">Insights</h4>
                        <span class="text-xs text-gray-400">
                            {{ $year === now()->year ? 'Year-to-date' : 'Full-year' }} comparison
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        @foreach($insights as $insight)
                            @php
                                $tone = $insight['tone'] ?? 'neutral';
                                $cardClasses = [
                                    'positive' => 'border-emerald-200 bg-emerald-50',
                                    'negative' => 'border-rose-200 bg-rose-50',
                                    'neutral' => 'border-gray-200 bg-gray-50',
                                ];
                                $valueClasses = [
                                    'positive' => 'text-emerald-700',
                                    'negative' => 'text-rose-700',
                                    'neutral' => 'text-gray-700',
                                ];
                                $detailClasses = [
                                    'positive' => 'text-emerald-600/80',
                                    'negative' => 'text-rose-600/80',
                                    'neutral' => 'text-gray-500',
                                ];
                                $labelText = $insight['label'] ?? 'Insight';
                                $tooltipText = trim((string) ($insight['detail'] ?? ''));
                                if ($tooltipText === '') {
                                    $tooltipText = 'Comparison versus previous year or forecast baseline.';
                                }
                                if (str_contains((string) $labelText, 'Peak Month Share')) {
                                    $tooltipText = 'Share of the total forecasted annual maintenance cost that comes from the single highest-forecast month.';
                                }
                            @endphp
                            <div class="rounded-xl border p-4 {{ $cardClasses[$tone] ?? $cardClasses['neutral'] }}">
                                <div class="flex items-center gap-1 mb-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $labelText }}</span>
                                    <flux:tooltip :content="$tooltipText" position="top">
                                        <svg class="h-3.5 w-3.5 text-gray-400 hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9 9.75a.75.75 0 011.5 0v4a.75.75 0 01-1.5 0v-4z" clip-rule="evenodd" />
                                        </svg>
                                    </flux:tooltip>
                                </div>
                                <p class="text-2xl font-bold {{ $valueClasses[$tone] ?? $valueClasses['neutral'] }}">{{ $insight['value_text'] ?? 'N/A' }}</p>
                                <p class="text-xs mt-1 {{ $detailClasses[$tone] ?? $detailClasses['neutral'] }}">{{ $insight['detail'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-16 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-700">No Forecast Data</h3>
            <p class="mt-1 text-sm text-gray-500">Unable to generate forecast. Please ensure maintenance records exist.</p>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function getMaintenanceForecastPayload() {
            const payloadNode = document.getElementById('maintenanceForecastPayload');

            if (!payloadNode) {
                return { forecastYear: new Date().getFullYear(), monthlyForecasts: [] };
            }

            try {
                return JSON.parse(payloadNode.textContent || '{}');
            } catch (error) {
                return { forecastYear: new Date().getFullYear(), monthlyForecasts: [] };
            }
        }

        function destroyMaintenanceCharts() {
            if (window.maintenanceChartInstance) {
                window.maintenanceChartInstance.destroy();
                window.maintenanceChartInstance = null;
            }

            if (window.jobCountChartInstance) {
                window.jobCountChartInstance.destroy();
                window.jobCountChartInstance = null;
            }
        }

        function renderMaintenanceCharts() {
            if (typeof Chart === 'undefined' || typeof ApexCharts === 'undefined') {
                setTimeout(renderMaintenanceCharts, 100);
                return;
            }

            const maintenanceChartElement = document.getElementById('maintenanceChart');
            const jobCountChartElement = document.getElementById('jobCountChart');
            const payload = getMaintenanceForecastPayload();
            const monthlyForecasts = Array.isArray(payload.monthlyForecasts) ? payload.monthlyForecasts : [];

            if (!maintenanceChartElement || monthlyForecasts.length === 0) {
                destroyMaintenanceCharts();
                return;
            }

            const categories = monthlyForecasts.map(f => f.month_name);
            const forecastCostData = monthlyForecasts.map(f => Number(f.forecasted_cost || 0));
            const actualCostData = monthlyForecasts.map(f => Number(f.actual_cost || 0));
            const previousYearCostData = monthlyForecasts.map(f => Number(f.previous_year_cost || 0));
            const forecastJobCounts = monthlyForecasts.map(f => Math.round(Number(f.maintenance_count_estimate || 0)));
            const actualJobCounts = monthlyForecasts.map(f => Math.round(Number(f.actual_job_count || 0)));

            const chartCtx = maintenanceChartElement.getContext('2d');

            const actualGradient = chartCtx.createLinearGradient(0, 0, 0, maintenanceChartElement.parentElement.offsetHeight || 320);
            actualGradient.addColorStop(0, 'rgba(140, 197, 255, 0.25)');
            actualGradient.addColorStop(0.6, 'rgba(140, 197, 255, 0.05)');
            actualGradient.addColorStop(1, 'rgba(140, 197, 255, 0)');

            const forecastGradient = chartCtx.createLinearGradient(0, 0, 0, maintenanceChartElement.parentElement.offsetHeight || 320);
            forecastGradient.addColorStop(0, 'rgba(30, 27, 75, 0.2)');
            forecastGradient.addColorStop(0.6, 'rgba(30, 27, 75, 0.03)');
            forecastGradient.addColorStop(1, 'rgba(30, 27, 75, 0)');

            destroyMaintenanceCharts();

            window.maintenanceChartInstance = new Chart(maintenanceChartElement, {
                type: 'line',
                data: {
                    labels: categories,
                    datasets: [
                        {
                            label: 'Actual Cost',
                            data: actualCostData,
                            borderColor: '#8CC5FF',
                            backgroundColor: actualGradient,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#8CC5FF',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#8CC5FF',
                            pointHoverBorderColor: '#FFFFFF',
                            pointHoverBorderWidth: 3,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Forecasted Cost',
                            data: forecastCostData,
                            borderColor: '#1E1B4B',
                            backgroundColor: forecastGradient,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#1E1B4B',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#1E1B4B',
                            pointHoverBorderColor: '#FFFFFF',
                            pointHoverBorderWidth: 3,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Previous Year',
                            data: previousYearCostData,
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245, 158, 11, 0.08)',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointBackgroundColor: '#F59E0B',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#F59E0B',
                            pointHoverBorderColor: '#FFFFFF',
                            pointHoverBorderWidth: 3,
                            tension: 0.35,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E1B4B',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            titleFont: { size: 11, weight: '400' },
                            bodyFont: { size: 13, weight: '600' },
                            padding: { top: 8, bottom: 8, left: 14, right: 14 },
                            cornerRadius: 8,
                            displayColors: false,
                            caretSize: 6,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    label += '₱' + new Intl.NumberFormat('en-PH').format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false },
                            ticks: {
                                color: '#9CA3AF',
                                font: { size: 12 },
                                padding: 8,
                                callback: function(value) {
                                    if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
                                    return '₱' + (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false },
                            ticks: { color: '#9CA3AF', font: { size: 12 }, padding: 8 }
                        }
                    }
                }
            });

            if (jobCountChartElement) {
                const jobShareSeries = [
                    {
                        name: 'Forecasted Jobs',
                        data: forecastJobCounts.map((forecastCount, index) => {
                            const actualCount = actualJobCounts[index] || 0;
                            const total = forecastCount + actualCount;
                            return total > 0 ? (forecastCount / total) * 100 : 0;
                        })
                    },
                    {
                        name: 'Actual Jobs',
                        data: actualJobCounts.map((actualCount, index) => {
                            const forecastCount = forecastJobCounts[index] || 0;
                            const total = forecastCount + actualCount;
                            return total > 0 ? (actualCount / total) * 100 : 0;
                        })
                    }
                ];

                const jobCountOptions = {
                    chart: {
                        type: 'bar',
                        height: 320,
                        stacked: true,
                        stackType: '100%',
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 700
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 5,
                            barHeight: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: categories,
                        min: 0,
                        max: 100,
                        tickAmount: 5,
                        labels: {
                            formatter: function (val) {
                                return Math.round(Number(val)) + '%';
                            },
                            style: {
                                fontSize: '12px',
                                colors: '#6B7280'
                            }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '11px',
                                colors: '#6B7280'
                            }
                        }
                    },
                    stroke: {
                        show: true,
                        width: 1,
                        colors: ['#FFFFFF']
                    },
                    fill: {
                        opacity: 1
                    },
                    legend: {
                        show: false
                    },
                    colors: ['#1E1B4B', '#8CC5FF'],
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function (val, { seriesIndex, dataPointIndex }) {
                                const percentage = Number(val || 0).toFixed(1);
                                const forecastCount = forecastJobCounts[dataPointIndex] || 0;
                                const actualCount = actualJobCounts[dataPointIndex] || 0;

                                if (seriesIndex === 0) {
                                    return `${percentage}% (${forecastCount} jobs)`;
                                }

                                return `${percentage}% (${actualCount} jobs)`;
                            }
                        },
                        theme: 'dark'
                    },
                    grid: {
                        borderColor: '#F3F4F6',
                        strokeDashArray: 0,
                        xaxis: { lines: { show: true } },
                        yaxis: { lines: { show: false } }
                    }
                };

                window.jobCountChartInstance = new ApexCharts(
                    jobCountChartElement,
                    {
                        series: jobShareSeries,
                        ...jobCountOptions
                    }
                );

                window.jobCountChartInstance.render();
            }
        }

        function downloadMaintenanceChart(format) {
            const payload = getMaintenanceForecastPayload();
            const forecastYear = Number(payload.forecastYear || new Date().getFullYear());

            if (!window.maintenanceChartInstance) {
                return;
            }

            const canvas = document.getElementById('maintenanceChart');
            if (!canvas) {
                return;
            }

            if (format === 'svg') {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png', 1.0);
                link.download = `maintenance-costs-${forecastYear}.png`;
                link.click();
            } else if (format === 'png') {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png', 1.0);
                link.download = `maintenance-costs-${forecastYear}.png`;
                link.click();
            }
        }

        window.downloadMaintenanceCsv = function() {
            const payload = getMaintenanceForecastPayload();
            const monthlyForecasts = Array.isArray(payload.monthlyForecasts) ? payload.monthlyForecasts : [];
            const forecastYear = Number(payload.forecastYear || new Date().getFullYear());

            if (monthlyForecasts.length === 0) {
                return;
            }

            const csv = ['Month,Forecasted Cost,Actual Cost,Previous Year Cost,Forecasted Jobs,Actual Jobs'];
            monthlyForecasts.forEach(m => {
                csv.push(
                    `${m.month_name},${Number(m.forecasted_cost || 0)},${Number(m.actual_cost || 0)},${Number(m.previous_year_cost || 0)},${Math.round(Number(m.maintenance_count_estimate || 0))},${Math.round(Number(m.actual_job_count || 0))}`
                );
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `maintenance-costs-${forecastYear}.csv`;
            a.click();
        };

        const scheduleMaintenanceChartRender = () => setTimeout(renderMaintenanceCharts, 0);

        if (!window.__maintenanceForecastListenersBound) {
            window.__maintenanceForecastListenersBound = true;

            document.addEventListener('DOMContentLoaded', scheduleMaintenanceChartRender);
            document.addEventListener('livewire:navigated', scheduleMaintenanceChartRender);

            document.addEventListener('livewire:init', () => {
                Livewire.on('maintenance-forecast-updated', scheduleMaintenanceChartRender);
            });
        }

        scheduleMaintenanceChartRender();
    </script>
</div>
