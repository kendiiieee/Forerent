<div class="bg-white rounded-lg shadow-md p-6" wire:init="loadForecast">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[#070642]">Revenue Forecast</h2>
    </div>

    @if($error)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if($warning)
        <div class="bg-amber-100 border border-amber-300 text-amber-900 px-4 py-3 rounded-xl mb-4">
            <strong>Notice:</strong> {{ $warning }}
        </div>
    @endif

    <script type="application/json" id="revenueForecastPayload">{!! json_encode([
        'forecastYear' => $forecastYear,
        'monthlyForecasts' => $monthlyForecasts,
    ]) !!}</script>

    @if(!empty($monthlyForecasts))
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-[#070642]">Monthly Revenue Forecast - {{ $forecastYear }}</h3>
                </div>

                <div class="flex items-center gap-6">
                    <!-- Download Dropdown -->
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
                            <button type="button" @click="downloadForecastChart('svg'); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download SVG
                            </button>
                            <button type="button" @click="downloadForecastChart('png'); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download PNG
                            </button>
                            <button type="button" @click="downloadForecastCSV(); open = false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors text-gray-600">
                                Download CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Legend below header --}}
            <div class="flex items-center gap-5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #8CC5FF;"></span>
                    <span class="text-sm text-gray-500 font-medium">Actual Earnings</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #1E1B4B;"></span>
                    <span class="text-sm text-gray-500 font-medium">Forecasted Revenue</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-sm" style="background-color: #F59E0B;"></span>
                    <span class="text-sm text-gray-500 font-medium">Previous Year</span>
                </div>
            </div>

            <div class="relative flex-1 min-h-80" wire:ignore>
                <canvas id="revenueChart"></canvas>
            </div>

            @if(!empty($insights))
                <div class="mt-6 border-t border-gray-100 pt-5">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.15em]">Insights</h4>
                        <span class="text-xs text-gray-400">
                            {{ $forecastYear === now()->year ? 'Year-to-date' : 'Full-year' }} comparison
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
                            @endphp
                            <div class="rounded-xl border p-4 {{ $cardClasses[$tone] ?? $cardClasses['neutral'] }}">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ $insight['label'] ?? 'Insight' }}</p>
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
            <h3 class="mt-2 text-lg font-medium text-gray-700">Loading Forecast</h3>
            <p class="mt-1 text-sm text-gray-500">Generating revenue predictions...</p>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function getRevenueForecastPayload() {
            const payloadNode = document.getElementById('revenueForecastPayload');

            if (!payloadNode) {
                return { forecastYear: new Date().getFullYear(), monthlyForecasts: [] };
            }

            try {
                return JSON.parse(payloadNode.textContent || '{}');
            } catch (error) {
                return { forecastYear: new Date().getFullYear(), monthlyForecasts: [] };
            }
        }

        function renderRevenueChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(renderRevenueChart, 100);
                return;
            }

            const chartElement = document.getElementById('revenueChart');
            const payload = getRevenueForecastPayload();
            const monthlyForecasts = Array.isArray(payload.monthlyForecasts) ? payload.monthlyForecasts : [];

            if (!chartElement || monthlyForecasts.length === 0) {
                if (window.revenueChartInstance) {
                    window.revenueChartInstance.destroy();
                    window.revenueChartInstance = null;
                }

                return;
            }

            const categories = monthlyForecasts.map(f => f.month_name);
            const actualData = monthlyForecasts.map(f => Number(f.actual_revenue || 0));
            const forecastData = monthlyForecasts.map(f => Number(f.forecasted_revenue || 0));
            const previousYearData = monthlyForecasts.map(f => Number(f.previous_year_revenue || 0));
            const chartCtx = chartElement.getContext('2d');

            const actualGradient = chartCtx.createLinearGradient(0, 0, 0, chartElement.parentElement.offsetHeight || 320);
            actualGradient.addColorStop(0, 'rgba(140, 197, 255, 0.25)');
            actualGradient.addColorStop(0.6, 'rgba(140, 197, 255, 0.05)');
            actualGradient.addColorStop(1, 'rgba(140, 197, 255, 0)');

            const forecastGradient = chartCtx.createLinearGradient(0, 0, 0, chartElement.parentElement.offsetHeight || 320);
            forecastGradient.addColorStop(0, 'rgba(30, 27, 75, 0.2)');
            forecastGradient.addColorStop(0.6, 'rgba(30, 27, 75, 0.03)');
            forecastGradient.addColorStop(1, 'rgba(30, 27, 75, 0)');

            if (window.revenueChartInstance) {
                window.revenueChartInstance.destroy();
            }

            window.revenueChartInstance = new Chart(chartElement, {
                type: 'line',
                data: {
                    labels: categories,
                    datasets: [
                        {
                            label: 'Actual Earnings',
                            data: actualData,
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
                            label: 'Forecasted Revenue',
                            data: forecastData,
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
                            data: previousYearData,
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
        }

        function getForecastYear() {
            const payload = getRevenueForecastPayload();
            return Number(payload.forecastYear || new Date().getFullYear());
        }

        function downloadForecastChart(format) {
            const forecastYear = getForecastYear();
            const canvas = document.getElementById('revenueChart');

            if (!canvas) return;

            if (format === 'svg') {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png', 1.0);
                link.download = `revenue-forecast-${forecastYear}.png`;
                link.click();
            } else if (format === 'png') {
                const element = canvas.parentElement;
                if (element) {
                    html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        logging: false
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/png');
                        link.download = `revenue-forecast-${forecastYear}.png`;
                        link.click();
                    });
                }
            }
        }

        function downloadForecastCSV() {
            const payload = getRevenueForecastPayload();
            const monthlyForecasts = Array.isArray(payload.monthlyForecasts) ? payload.monthlyForecasts : [];
            const forecastYear = getForecastYear();

            if (monthlyForecasts.length === 0) return;

            let csv = 'Month,Actual Earnings,Forecasted Revenue,Previous Year Revenue\n';
            monthlyForecasts.forEach(f => {
                csv += `"${f.month_name}",${f.actual_revenue || 0},${f.forecasted_revenue},${f.previous_year_revenue || 0}\n`;
            });

            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
            link.download = `revenue-forecast-${forecastYear}.csv`;
            link.click();
        }

        const scheduleRevenueChartRender = () => setTimeout(renderRevenueChart, 0);

        if (!window.__revenueForecastListenersBound) {
            window.__revenueForecastListenersBound = true;

            document.addEventListener('DOMContentLoaded', scheduleRevenueChartRender);
            document.addEventListener('livewire:navigated', scheduleRevenueChartRender);

            document.addEventListener('livewire:init', () => {
                Livewire.on('revenue-forecast-updated', scheduleRevenueChartRender);
            });
        }

        scheduleRevenueChartRender();
    </script>
</div>
