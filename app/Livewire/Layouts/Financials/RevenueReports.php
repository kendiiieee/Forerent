<?php

namespace App\Livewire\Layouts\Financials;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\MaintenanceLog;
use Carbon\Carbon;

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
        ]);
    }

    public function getInflowOutflowData(): array
    {
        $year = Carbon::now()->year;
        $driver = Transaction::query()->getConnection()->getDriverName();
        $transactionMonthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM transaction_date)::int'
            : 'MONTH(transaction_date)';
        $maintenanceMonthExpr = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM completion_date)::int'
            : 'MONTH(completion_date)';

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $income = array_fill(0, 12, 0);
        $expenses = array_fill(0, 12, 0);

        // Revenue/inflow source: credit transactions.
        $monthlyIncome = Transaction::where('category', 'Rent Payment')
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

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    public function getMaintenanceCostData(): array
    {
        $now = Carbon::now();

        $logs = MaintenanceLog::with(['request:request_id,category'])
            ->when($this->maintenanceBreakdownScope === 'month', function ($query) use ($now) {
                $query->whereYear('completion_date', $now->year)
                    ->whereMonth('completion_date', $now->month);
            }, function ($query) use ($now) {
                $query->whereYear('completion_date', $now->year);
            })
            ->get();

        $amountByCategory = [];
        foreach ($this->maintenanceCategories as $category) {
            $amountByCategory[$category] = 0;
        }

        foreach ($logs as $log) {
            $category = $log->request->category ?? null;
            if ($category && array_key_exists($category, $amountByCategory)) {
                $amountByCategory[$category] += (float) $log->cost;
            }
        }

        return [
            'labels' => array_keys($amountByCategory),
            'amounts' => array_values($amountByCategory),
        ];
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

        return view('livewire.layouts.financials.revenue-reports', [
            'inflowOutflowData' => $inflowOutflowData,
            'maintenanceCostData' => $maintenanceCostData,
            'maintenanceBreakdownLabel' => $this->getMaintenanceBreakdownLabel(),
        ]);
    }
}
