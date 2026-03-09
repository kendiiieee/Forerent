{{-- WRAPPER DIV: Required by Livewire to prevent "Root Tag Missing" error --}}
<div class="space-y-6">

    <livewire:layouts.maint-forecast />
    <div class=" my-4"></div>

    {{-- 1. KPI CARDS (Real Data) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($buildingData as $card)
        <div class="bg-[#070642] rounded-2xl p-5 text-white shadow-md relative overflow-hidden group">
            <p class="text-sm font-medium opacity-80 mb-1">{{ $card['name'] }}</p>
            <h3 class="text-3xl font-bold mb-4">₱ {{ number_format($card['cost']) }}</h3>
            <div class="flex items-center text-xs font-bold">
                @if($card['change_type'] === 'higher')
                <span class="text-[#00FF55] flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    {{ $card['change'] }}% higher
                </span>
                @elseif($card['change_type'] === 'lower')
                <span class="text-[#00FF55] flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                    {{ $card['change'] }}% lower
                </span>
                @else
                <span class="text-gray-300">Stable</span>
                @endif
                <span class="ml-1 font-normal opacity-70">from last month</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            initChart();
        });
        document.addEventListener('DOMContentLoaded', () => {
            initChart();
        });

        function initChart() {
            const ctx = document.getElementById('maintenanceChart');
            if (!ctx) return;

            if (window.myMaintenanceChart) {
                window.myMaintenanceChart.destroy();
            }

            // Pull data passed from PHP
            const labels = @json($chartLabels);
            const data = @json($chartData);

            window.myMaintenanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Projected Cost',
                        data: data,
                        borderColor: '#2B66F5',
                        backgroundColor: 'rgba(43, 102, 245, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#2B66F5',
                        pointRadius: 4,
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#FFFFFF',
                            titleColor: '#070642',
                            bodyColor: '#070642',
                            borderColor: '#E5E7EB',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '₱ ' + new Intl.NumberFormat('en-PH').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5],
                                color: '#F3F4F6'
                            },
                            ticks: {
                                font: {
                                    family: "'Open Sans', sans-serif"
                                },
                                color: '#9CA3AF'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9CA3AF'
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                }
            });
        }
    </script>
</div>