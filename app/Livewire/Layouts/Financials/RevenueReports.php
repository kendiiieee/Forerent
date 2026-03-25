<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\MaintenanceLog;
use App\Models\Transaction;
use Carbon\Carbon;
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
        ]);
    }

    public function getInflowOutflowData(): array
    {
        $year = Carbon::now()->year;

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $income = array_fill(0, 12, 0);
        $expenses = array_fill(0, 12, 0);

        // Call the protected scope from the Model instead of raw SQL
        $monthlyIncome = Transaction::monthlyRevenueSummary($year)->get();

        foreach ($monthlyIncome as $row) {
            $income[(int) $row->month - 1] = (float) $row->total;
        }

        $monthlyExpenses = MaintenanceLog::whereYear('completion_date', $year)
            ->selectRaw('CAST(EXTRACT(MONTH FROM completion_date) AS UNSIGNED) as month, SUM(cost) as total')
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
