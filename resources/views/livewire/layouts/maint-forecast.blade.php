<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Maintenance Cost Forecast</h2>
        
        <div class="flex items-center">
            <select wire:model.live="year" class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                @for($y = date('Y'); $y <= date('Y') + 3; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    @if($error)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if($forecast && ($forecast['is_fallback'] ?? false))
        <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-4">
            <strong>Notice:</strong> {{ $forecast['warning'] ?? 'Using fallback forecast data.' }}
        </div>
    @endif

    @if($hasData && $maintenanceStats)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-orange-700 mb-2">Annual Forecast</p>
                <p class="text-3xl font-bold text-orange-600">₱{{ number_format($forecast['total_annual_cost'] ?? 0, 0) }}</p>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-yellow-700 mb-2">Monthly Average</p>
                <p class="text-3xl font-bold text-yellow-600">₱{{ number_format($forecast['average_monthly_cost'] ?? 0, 0) }}</p>
            </div>
        </div>
    @endif

    @if($forecast && isset($forecast['success']) && $forecast['success'])
        <!-- Maintenance Chart -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Monthly Maintenance Costs - {{ $year }}</h3>
            <div id="maintenanceChart" style="height: 400px;"></div>
        </div>

        <!-- Monthly Forecast Table -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Forecast Details</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Month</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Forecasted Cost</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Est. Jobs</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Urgency Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($forecast['monthly_forecasts'] ?? []) as $monthly)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $monthly['month_name'] ?? '' }}</td>
                            <td class="px-4 py-3 text-gray-700">₱{{ number_format($monthly['forecasted_cost'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $monthly['maintenance_count_estimate'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ number_format($monthly['urgency_estimate'] ?? 0, 1) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Maintenance Schedule -->
        @if(!empty($forecast['maintenance_schedule']))
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recommended Maintenance Schedule (Top 5)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Month</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Category</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Priority</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Estimated Cost</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($forecast['maintenance_schedule'] ?? []) as $schedule)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $schedule['month_name'] ?? '' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $schedule['category'] ?? '' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ ($schedule['priority'] ?? '') == 'High' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $schedule['priority'] ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">₱{{ number_format($schedule['estimated_cost'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $schedule['reason'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('livewire:navigated', () => { renderMaintenanceChart(); });
            document.addEventListener('DOMContentLoaded', () => { renderMaintenanceChart(); });

            function renderMaintenanceChart() {
                const forecast = @json($forecast);
                const monthlyForecasts = forecast['monthly_forecasts'] || [];
                
                if (!monthlyForecasts || monthlyForecasts.length === 0) return;

                const categories = monthlyForecasts.map(f => f.month_name);
                const series = [
                    {
                        name: 'Forecasted Cost',
                        data: monthlyForecasts.map(f => f.forecasted_cost)
                    }
                ];

                const options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 6,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return '₱' + (val / 1000).toFixed(0) + 'K';
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ['#304758']
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: categories,
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Cost (₱)',
                            style: {
                                fontSize: '13px',
                                fontWeight: 600
                            }
                        },
                        labels: {
                            formatter: function (val) {
                                return '₱' + (val / 1000).toFixed(0) + 'K';
                            }
                        }
                    },
                    fill: {
                        opacity: 0.9
                    },
                    colors: ['#F59E0B'],
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return '₱' + val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            }
                        },
                        theme: 'light'
                    },
                    grid: {
                        borderColor: '#E7E7E7',
                        strokeDashArray: 4,
                        show: true
                    },
                    states: {
                        hover: {
                            filter: {
                                type: 'darken',
                                value: 0.15
                            }
                        },
                        active: {
                            filter: {
                                type: 'darken',
                                value: 0.15
                            }
                        }
                    }
                };

                // Destroy existing chart if it exists
                if (window.maintenanceChartInstance) {
                    window.maintenanceChartInstance.destroy();
                }

                // Create new chart
                window.maintenanceChartInstance = new ApexCharts(
                    document.getElementById('maintenanceChart'),
                    {
                        series,
                        ...options
                    }
                );
                
                window.maintenanceChartInstance.render();
            }
        </script>
    @elseif($isGenerating)
        <div class="text-center py-16 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m0 0c0-1 1-3 3-3s3 1 3 3-1 3-3 3-3-1-3-3m0 0c0 1-1 3-3 3s-3-1-3-3 1-3 3-3 3 1 3 3" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-700">Generating Forecast</h3>
            <p class="mt-1 text-sm text-gray-500">Processing maintenance data...</p>
        </div>
    @else
        <div class="text-center py-16 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-700">No Forecast Data</h3>
            <p class="mt-1 text-sm text-gray-500">Unable to generate forecast. Please ensure maintenance records exist.</p>
        </div>
    @endif
</div>