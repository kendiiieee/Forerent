<div>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[#070642]">Financial Overview</h2>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Revenue Breakdown (monthly) --}}
        <div class="xl:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-lg flex flex-col">
            <div class="flex items-start justify-between gap-2 sm:gap-3 mb-4">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-bold text-[#070642] leading-tight">Revenue Breakdown</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Monthly totals (excluding security deposits)</p>
                </div>
            </div>

            @php
                $monthlyRows = $revenueBreakdown ?? [];
                $categories = [];
                foreach ($monthlyRows as $row) {
                    if (!isset($row['categories']) || !is_array($row['categories'])) {
                        continue;
                    }
                    foreach ($row['categories'] as $cat => $amt) {
                        $categories[$cat] = true;
                    }
                }
                $categoryList = array_keys($categories);
            @endphp

            <div class="overflow-x-auto">
                <table id="revenue-breakdown-table" class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wide">
                            <th class="py-2 pr-4">Month</th>
                            @foreach($categoryList as $cat)
                                <th class="py-2 pr-4 text-right whitespace-nowrap">{{ $cat }}</th>
                            @endforeach
                            <th class="py-2 pr-4 text-right whitespace-nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($categoryList))
                            <tr>
                                <td colspan="1" class="py-3 text-sm text-gray-500">No revenue data available.</td>
                            </tr>
                        @else
                            @foreach($monthlyRows as $monthEntry)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 pr-4 text-gray-600">{{ $monthEntry['month_name'] ?? '' }}</td>
                                    @php $rowTotal = 0; @endphp
                                    @foreach($categoryList as $cat)
                                        @php
                                            $amt = $monthEntry['categories'][$cat] ?? 0;
                                            $rowTotal += $amt;
                                        @endphp
                                        <td class="py-2 pr-4 text-right font-semibold">₱ {{ number_format($amt, 2) }}</td>
                                    @endforeach
                                    <td class="py-2 text-right font-semibold">₱ {{ number_format($rowTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
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
                <div class="w-full mt-4 sm:mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                    @foreach($maintenanceCostData['labels'] as $i => $label)
                        @php
                            $pct = $totalAmount > 0 ? round(($maintenanceCostData['amounts'][$i] / $totalAmount) * 100, 1) : 0;
                            $spanClass = $loop->last ? 'sm:col-span-2' : '';
                        @endphp
                        <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gray-50/80 group cursor-default transition-all duration-200 hover:bg-gray-100/80 {{ $spanClass }}">
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
        'maintenanceCostData' => $maintenanceCostData,
    ]) !!}</script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Removed the inflow/outflow line chart. The page now shows Maintenance Expenses Breakdown only.

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
                    initMaintenanceDonut(payload);
                } catch (e) {
                    console.error('Failed to parse revenue reports payload', e);
                }
            }

            if (!window.__revenueReportsChartsListenerBound) {
                window.__revenueReportsChartsListenerBound = true;
                Livewire.on('update-charts', (event) => {
                    const payload = Array.isArray(event) ? event[0] : event;
                    initMaintenanceDonut(payload);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', bootRevenueReportCharts);
        document.addEventListener('livewire:navigated', bootRevenueReportCharts);
    </script>
</div>
