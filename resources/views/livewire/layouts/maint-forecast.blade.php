<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Maintenance Cost Forecast</h2>

        <div class="flex items-center">
            <x-dropdown label="{{ $year }}" width="w-36" tooltip="Select forecast year">
                @for($y = date('Y'); $y <= date('Y') + 3; $y++)
                    <x-dropdown-item wire:click="$set('year', {{ $y }})" :active="$year == $y">
                        {{ $y }}
                    </x-dropdown-item>
                @endfor
            </x-dropdown>
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
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700 mb-2">Annual Forecast</p>
                <p class="text-3xl font-bold text-blue-600">₱{{ number_format($forecast['total_annual_cost'] ?? 0, 0) }}</p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700 mb-2">Monthly Average</p>
                <p class="text-3xl font-bold text-blue-600">₱{{ number_format($forecast['average_monthly_cost'] ?? 0, 0) }}</p>
            </div>
        </div>
    @endif

    @if($forecast && isset($forecast['success']) && $forecast['success'])
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">
            <div class="bg-white rounded-2xl shadow-lg p-6 lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-[#070642]">Monthly Maintenance Costs</h3>
                    <x-dropdown label="Download" width="w-44" tooltip="Download chart as image or CSV">
                        <x-dropdown-item @click="open = false; window.maintenanceChartInstance?.dataURI().then(uri => { const a = document.createElement('a'); a.href = uri.imgURI; a.download = 'maintenance-costs-{{ $year }}.png'; a.click(); })">
                            Download PNG
                        </x-dropdown-item>
                        <x-dropdown-item @click="open = false; window.maintenanceChartInstance?.dataURI({type: 'image/svg+xml'}).then(uri => { const a = document.createElement('a'); a.href = uri.imgURI; a.download = 'maintenance-costs-{{ $year }}.svg'; a.click(); })">
                            Download SVG
                        </x-dropdown-item>
                        <x-dropdown-item @click="open = false; window.downloadMaintenanceCsv()">
                            Download CSV
                        </x-dropdown-item>
                    </x-dropdown>
                </div>
                <div id="maintenanceChart" style="height: 400px;"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 lg:col-span-1 flex flex-col">
                <h3 class="text-xl font-bold text-[#070642] mb-2">Job Count Forecast</h3>
                <p class="text-sm text-gray-500 mb-4">Estimated maintenance jobs per month</p>
                <div id="jobCountChart" class="flex-1" style="height: 400px;"></div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('livewire:navigated', () => { renderMaintenanceChart(); });
            document.addEventListener('DOMContentLoaded', () => { renderMaintenanceChart(); });

            window.downloadMaintenanceCsv = function() {
                const forecast = @json($forecast);
                const monthlyForecasts = forecast['monthly_forecasts'] || [];
                const csv = ['Month,Cost'];
                monthlyForecasts.forEach(m => csv.push(m.month_name + ',' + m.forecasted_cost));
                const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'maintenance-costs-{{ $year }}.csv';
                a.click();
            };

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
                const jobCountSeries = [
                    {
                        name: 'Est. Jobs',
                        data: monthlyForecasts.map(f => Math.round(Number(f.maintenance_count_estimate || 0)))
                    }
                ];

                const options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: false
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
                            columnWidth: '70%',
                            borderRadius: 8,
                            borderRadiusApplication: 'end'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: false
                    },
                    xaxis: {
                        categories: categories,
                        labels: {
                            style: {
                                fontSize: '12px',
                                colors: '#6B7280',
                                fontFamily: 'Open Sans, sans-serif'
                            }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return '₱' + val.toLocaleString();
                            },
                            style: {
                                fontSize: '12px',
                                colors: '#6B7280',
                                fontFamily: 'Open Sans, sans-serif'
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    colors: ['#0C0B50'],
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return '₱' + val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            }
                        },
                        theme: 'dark'
                    },
                    grid: {
                        borderColor: '#F3F4F6',
                        strokeDashArray: 0,
                        show: true,
                        xaxis: { lines: { show: false } },
                        yaxis: { lines: { show: true } }
                    },
                    states: {
                        hover: {
                            filter: {
                                type: 'darken',
                                value: 0.1
                            }
                        },
                        active: {
                            filter: {
                                type: 'darken',
                                value: 0.1
                            }
                        }
                    }
                };

                // Destroy existing chart if it exists
                if (window.maintenanceChartInstance) {
                    window.maintenanceChartInstance.destroy();
                }

                if (window.jobCountChartInstance) {
                    window.jobCountChartInstance.destroy();
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

                const jobCountOptions = {
                    chart: {
                        type: 'bar',
                        height: 400,
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
                            borderRadius: 6,
                            borderRadiusApplication: 'end',
                            barHeight: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return Math.round(val);
                        },
                        style: {
                            fontSize: '11px',
                            colors: ['#ffffff']
                        }
                    },
                    xaxis: {
                        categories: categories,
                        labels: {
                            formatter: function (val) {
                                return Math.round(val);
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
                        show: false
                    },
                    fill: {
                        opacity: 1
                    },
                    colors: ['#0C0B50'],
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return Math.round(val) + ' jobs';
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
                    document.getElementById('jobCountChart'),
                    {
                        series: jobCountSeries,
                        ...jobCountOptions
                    }
                );

                window.jobCountChartInstance.render();
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
