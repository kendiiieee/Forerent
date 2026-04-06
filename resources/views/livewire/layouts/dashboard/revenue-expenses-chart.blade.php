<div class="bg-white rounded-2xl p-6 shadow-lg h-full flex flex-col" wire:ignore>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-[#070642]">Revenue vs Expenses</h3>
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
        <canvas id="revenueExpensesChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function initRevenueExpensesChart() {
            const ctx = document.getElementById('revenueExpensesChart');
            if (!ctx) return;

            if (window.myRevenueExpensesChart) {
                window.myRevenueExpensesChart.destroy();
            }

            const labels = @json($monthlyLabels);
            const revenueData = @json($monthlyRevenue);
            const expensesData = @json($monthlyExpenses);

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

            window.myRevenueExpensesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueData,
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
                            data: expensesData,
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
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1E1B4B',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            titleFont: {
                                size: 11,
                                weight: '400'
                            },
                            bodyFont: {
                                size: 13,
                                weight: '600'
                            },
                            padding: {
                                top: 8,
                                bottom: 8,
                                left: 14,
                                right: 14
                            },
                            cornerRadius: 8,
                            displayColors: false,
                            caretSize: 6,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₱' + new Intl.NumberFormat('en-PH').format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: {
                                display: false
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.04)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    size: 12
                                },
                                padding: 8,
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return '₱' + (value / 1000000).toFixed(1) + 'M';
                                    }
                                    return '₱' + (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        },
                        x: {
                            border: {
                                display: false
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    size: 12
                                },
                                padding: 8
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', initRevenueExpensesChart);
        document.addEventListener('livewire:navigated', initRevenueExpensesChart);

        document.addEventListener('livewire:init', () => {
            Livewire.on('dashboard-refresh-charts', () => {
                setTimeout(initRevenueExpensesChart, 0);
            });
        });
    </script>
</div>
