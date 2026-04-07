<div>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[#070642]">Financial Overview</h2>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Financial Inflows and Outflows --}}
        <div class="xl:col-span-2 bg-white rounded-2xl p-6 shadow-lg flex flex-col min-w-0" wire:ignore>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-[#070642]">Financial Inflows and Outflows</h3>
                <div class="flex items-center gap-5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm" style="background-color: #8CC5FF;"></span>
                        <span class="text-sm text-gray-500 font-medium">Revenue</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm" style="background-color: #1E1B4B;"></span>
                        <span class="text-sm text-gray-500 font-medium">Expenses</span>
                    </div>
                </div>
            </div>
            <div class="relative flex-1 min-h-80">
                <canvas id="inflowOutflowChart"></canvas>
            </div>
        </div>

        {{-- Maintenance Expenses Breakdown --}}
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg flex flex-col">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-2 sm:gap-3 mb-4 sm:mb-6">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-bold text-[#070642] leading-tight">Maintenance Expenses Breakdown</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $maintenanceBreakdownLabel }}</p>
                </div>
                <div class="shrink-0" x-data="{ open: false }" @click.away="open = false" @keydown.escape.stop="open = false">
                    <div class="relative">
                        <button
                            @click="open = !open"
                            type="button"
                            class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-[#070642] shadow-sm hover:border-gray-300 transition-all focus:outline-none focus:ring-0"
                        >
                            <span>{{ $maintenanceBreakdownScope === 'month' ? 'Current Month' : 'Whole Year' }}</span>
                            <svg :class="{ 'rotate-180': open }" class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition.origin.top.right
                            style="display: none;"
                            class="absolute right-0 origin-top-right z-30 w-44 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
                        >
                            <button
                                wire:click="$set('maintenanceBreakdownScope', 'month')"
                                @click="open = false"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors {{ $maintenanceBreakdownScope === 'month' ? 'text-[#070642] font-semibold bg-gray-50' : 'text-gray-600' }}"
                            >
                                Current Month
                            </button>
                            <button
                                wire:click="$set('maintenanceBreakdownScope', 'year')"
                                @click="open = false"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors {{ $maintenanceBreakdownScope === 'year' ? 'text-[#070642] font-semibold bg-gray-50' : 'text-gray-600' }}"
                            >
                                Whole Year
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Donut Chart --}}
            <div class="flex-1 flex flex-col items-center justify-center">
                <div wire:ignore id="maintenanceBreakdownChart" style="height: 280px; width: 100%; max-width: 280px;"></div>

                @php
                    $colors = ['#1a237e', '#4fc3f7', '#8CC5FF', '#1E1B4B', '#B2CBFF'];
                    $totalAmount = array_sum($maintenanceCostData['amounts']);
                @endphp

                {{-- Legend items --}}
                <div class="w-full mt-4 sm:mt-6 space-y-2 sm:space-y-3">
                    @foreach($maintenanceCostData['labels'] as $i => $label)
                        @php
                            $pct = $totalAmount > 0 ? round(($maintenanceCostData['amounts'][$i] / $totalAmount) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-gray-100/80">
                            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full shrink-0 ring-4 transition-all duration-200"
                                  style="background-color: {{ $colors[$i % count($colors)] }}; --tw-ring-color: {{ $colors[$i % count($colors)] }}1a;"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-400 uppercase tracking-wider">{{ $label }}</p>
                                <p class="text-xs sm:text-sm font-bold text-[#070642] truncate">₱ {{ number_format($maintenanceCostData['amounts'][$i] ?? 0, 2) }}</p>
                            </div>
                            <span class="text-sm sm:text-base font-bold shrink-0" style="color: {{ $colors[$i % count($colors)] }}">{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="revenueReportsPayload">{!! json_encode([
        'inflowOutflowData' => $inflowOutflowData,
        'maintenanceCostData' => $maintenanceCostData,
    ]) !!}</script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function initInflowOutflowChart(payload) {
            const ctx = document.getElementById('inflowOutflowChart');
            if (!ctx) return;

            if (window.myInflowOutflowChart) {
                window.myInflowOutflowChart.destroy();
            }

            const data = payload?.inflowOutflowData;
            if (!data) return;

            const chartCtx = ctx.getContext('2d');

            // Revenue gradient fill
            const revenueGradient = chartCtx.createLinearGradient(0, 0, 0, ctx.parentElement.offsetHeight || 320);
            revenueGradient.addColorStop(0, 'rgba(140, 197, 255, 0.25)');
            revenueGradient.addColorStop(0.6, 'rgba(140, 197, 255, 0.05)');
            revenueGradient.addColorStop(1, 'rgba(140, 197, 255, 0)');

            // Expenses gradient fill
            const expensesGradient = chartCtx.createLinearGradient(0, 0, 0, ctx.parentElement.offsetHeight || 320);
            expensesGradient.addColorStop(0, 'rgba(30, 27, 75, 0.2)');
            expensesGradient.addColorStop(0.6, 'rgba(30, 27, 75, 0.03)');
            expensesGradient.addColorStop(1, 'rgba(30, 27, 75, 0)');

            window.myInflowOutflowChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: data.income,
                            borderColor: '#8CC5FF',
                            backgroundColor: revenueGradient,
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
                            label: 'Expenses',
                            data: data.expenses,
                            borderColor: '#1E1B4B',
                            backgroundColor: expensesGradient,
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

        function initMaintenanceDonut(payload) {
            const node = document.querySelector('#maintenanceBreakdownChart');
            if (!node) return;

            const data = payload?.maintenanceCostData;
            if (!data) return;

            if (window.revenueReportCharts && window.revenueReportCharts.maintenanceBreakdown) {
                window.revenueReportCharts.maintenanceBreakdown.destroy();
            }
            window.revenueReportCharts = window.revenueReportCharts || {};

            const total = (data.amounts || []).reduce((sum, v) => sum + Number(v || 0), 0);

            const options = {
                series: data.amounts,
                chart: {
                    type: 'donut',
                    height: 280,
                    toolbar: { show: false }
                },
                labels: data.labels,
                colors: ['#1a237e', '#4fc3f7', '#8CC5FF', '#1E1B4B', '#B2CBFF'],
                stroke: {
                    width: 3,
                    colors: ['#ffffff']
                },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '12px',
                                    color: '#9CA3AF',
                                    offsetY: -8
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 700,
                                    color: '#070642',
                                    offsetY: 4,
                                    formatter: function(val) {
                                        return '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '12px',
                                    color: '#9CA3AF',
                                    formatter: function() {
                                        return '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { show: false },
                tooltip: {
                    enabled: true,
                    style: { fontSize: '13px' },
                    y: {
                        formatter: function(val) {
                            return '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                },
                states: {
                    hover: {
                        filter: { type: 'darken', value: 0.9 }
                    }
                },
                noData: { text: 'No maintenance expenses data' }
            };

            window.revenueReportCharts.maintenanceBreakdown = new ApexCharts(node, options);
            window.revenueReportCharts.maintenanceBreakdown.render();
        }

        function bootRevenueReportCharts() {
            if (typeof Chart === 'undefined' || typeof ApexCharts === 'undefined') {
                setTimeout(bootRevenueReportCharts, 100);
                return;
            }

            const payloadNode = document.getElementById('revenueReportsPayload');
            if (payloadNode) {
                try {
                    const payload = JSON.parse(payloadNode.textContent || '{}');
                    initInflowOutflowChart(payload);
                    initMaintenanceDonut(payload);
                } catch (e) {
                    console.error('Failed to parse revenue reports payload', e);
                }
            }

            if (!window.__revenueReportsChartsListenerBound) {
                window.__revenueReportsChartsListenerBound = true;
                Livewire.on('update-charts', (event) => {
                    const payload = Array.isArray(event) ? event[0] : event;
                    initInflowOutflowChart(payload);
                    initMaintenanceDonut(payload);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', bootRevenueReportCharts);
        document.addEventListener('livewire:navigated', bootRevenueReportCharts);
    </script>
</div>
