<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\MaintenanceLog;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RevenueReports extends Component
{
    public $maintenanceBreakdownScope = 'month'; // month | year

    private array $maintenanceCategories = ['Plumbing', 'Electrical', 'Structural', 'Appliance', 'Pest Control'];

    public function mount()
    {
        // No-op for now; charts are loaded from render data.
    }

    public function updatedMaintenanceBreakdownScope($value)
    {
        $this->dispatch('update-charts', [
            'inflowOutflowData' => $this->getInflowOutflowData(),
            'maintenanceCostData' => $this->getMaintenanceCostData(),
            'revenueBreakdown' => $this->getRevenueBreakdown(),
        ]);
    }

    public function getInflowOutflowData(): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $income = array_fill(0, 12, 0);
        $expenses = array_fill(0, 12, 0);

        $year = Carbon::now()->year;
        $driver = Transaction::query()->getConnection()->getDriverName();
        $transactionMonthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM transaction_date)::int'
            : 'CAST(MONTH(transaction_date) AS UNSIGNED)';
        $maintenanceMonthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM completion_date)::int'
            : 'CAST(MONTH(completion_date) AS UNSIGNED)';

        try {
            // Revenue/inflow source: all credit inflow transactions.
            $monthlyIncome = Transaction::query()
                ->creditInflows()
                ->whereYear('transaction_date', $year)
                ->selectRaw("{$transactionMonthExpr} as month, SUM(amount) as total")
                ->groupBy('month')
                ->get();

            foreach ($monthlyIncome as $row) {
                $income[(int) $row->month - 1] = (float) $row->total;
            }

            $monthlyExpenses = MaintenanceLog::whereYear('completion_date', $year)
                ->selectRaw("{$maintenanceMonthExpr} as month, SUM(cost) as total")
                ->groupBy('month')
                ->get();

            foreach ($monthlyExpenses as $row) {
                $expenses[(int) $row->month - 1] = (float) $row->total;
            }
        } catch (\Throwable $exception) {
            Log::warning('Revenue reports inflow/outflow query failed; returning empty dataset.', [
                'error' => $exception->getMessage(),
            ]);
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    public function getMaintenanceCostData(): array
    {
        $now = Carbon::now();

        try {
            $logs = MaintenanceLog::join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
                ->when($this->maintenanceBreakdownScope === 'month', function ($query) use ($now) {
                    $query->whereYear('maintenance_logs.completion_date', $now->year)
                        ->whereMonth('maintenance_logs.completion_date', $now->month);
                }, function ($query) use ($now) {
                    $query->whereYear('maintenance_logs.completion_date', $now->year);
                })
                ->selectRaw('maintenance_requests.category, SUM(maintenance_logs.cost) as total')
                ->groupBy('maintenance_requests.category')
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Revenue reports maintenance breakdown query failed; returning empty dataset.', [
                'error' => $exception->getMessage(),
            ]);

            $logs = collect();
        }

        $amountByCategory = [];
        foreach ($this->maintenanceCategories as $category) {
            $amountByCategory[$category] = 0;
        }

        foreach ($logs as $log) {
            $category = $log->category ?? null;
            if ($category && array_key_exists($category, $amountByCategory)) {
                $amountByCategory[$category] = (float) ($log->total ?? 0);
            }
        }

        return [
            'labels' => array_keys($amountByCategory),
            'amounts' => array_values($amountByCategory),
        ];
    }

    public function getRevenueBreakdown(): array
    {
        $year = Carbon::now()->year;
        $driver = Transaction::query()->getConnection()->getDriverName();
        $monthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM transaction_date)::int'
            : 'MONTH(transaction_date)';
        $categoryExpr = $driver === 'pgsql'
            ? "CASE WHEN COALESCE(reference_number, '') ILIKE 'ADV%' THEN 'Advance Payment' ELSE COALESCE(NULLIF(category, ''), 'Uncategorized') END"
            : "CASE WHEN LOWER(COALESCE(reference_number, '')) LIKE 'adv%' THEN 'Advance Payment' ELSE COALESCE(NULLIF(category, ''), 'Uncategorized') END";

        try {
            $rows = Transaction::query()
                ->creditInflows()
                ->whereRaw("LOWER({$categoryExpr}) NOT LIKE ?", ['%deposit%'])
                ->whereYear('transaction_date', $year)
                ->selectRaw("{$monthExpr} as month, {$categoryExpr} as category, SUM(amount) as total")
                ->groupByRaw("{$monthExpr}, {$categoryExpr}")
                ->orderByRaw($monthExpr)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Revenue reports breakdown query failed; returning empty dataset.', [
                'error' => $exception->getMessage(),
            ]);

            $rows = collect();
        }

        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $breakdown = [];
        for ($m = 1; $m <= 12; $m++) {
            $breakdown[$m] = [
                'month' => $m,
                'month_name' => $monthNames[$m],
                'categories' => [],
            ];
        }

        foreach ($rows as $row) {
            $month = (int) ($row->month ?? 0);
            if ($month < 1 || $month > 12) {
                continue;
            }

            $category = (string) ($row->category ?? 'Uncategorized');
            $breakdown[$month]['categories'][$category] =
                ($breakdown[$month]['categories'][$category] ?? 0) + (float) ($row->total ?? 0);
        }

        return array_values($breakdown);
    }

    public function getMaintenanceBreakdownLabel(): string
    {
        if ($this->maintenanceBreakdownScope === 'month') {
            return 'Current Month';
        }

        return 'Whole Year';
    }

    public function render()
    {
        $inflowOutflowData = $this->getInflowOutflowData();
        $maintenanceCostData = $this->getMaintenanceCostData();
        $revenueBreakdown = $this->getRevenueBreakdown();

        return view('livewire.layouts.financials.revenue-reports', [
            'inflowOutflowData' => $inflowOutflowData,
            'maintenanceCostData' => $maintenanceCostData,
            'maintenanceBreakdownLabel' => $this->getMaintenanceBreakdownLabel(),
            'revenueBreakdown' => $revenueBreakdown,
        ]);
    }
}
