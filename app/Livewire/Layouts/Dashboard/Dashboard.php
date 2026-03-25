<?php

namespace App\Livewire\Layouts\Dashboard;

use App\Models\Billing;
use App\Models\MaintenanceLog;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedDate;

    public $currentMonth;

    public $totalRentCollected;

    public $totalUncollectedRent;

    public $totalIncome;

    public $rentSummaryMonth;

    public $rentSummaryMonthOptions = [];

    public $rentSummaryPeriodLabel;

    public $rentCollectedPercentage = 0;

    public $uncollectedPercentage = 0;

    public $incomePercentage = 0;

    public $revenueCurrent;

    public $revenueTarget;

    public $expensesCurrent;

    public $expensesTarget;

    public $roiCurrent;

    public $roiTarget;

    // Monthly data for graphs
    public $monthlyLabels = [];

    public $monthlyRevenue = [];

    public $monthlyExpenses = [];

    public $monthlyRentCollected = [];

    public $totalUnits = 0;

    public $fullyBookedUnits = 0;

    public $availableUnits = 0;

    public $vacantUnits = 0;

    public function mount()
    {
        $this->selectedDate = Carbon::today();
        $this->currentMonth = Carbon::now()->format('F Y');
        $this->initializeRentSummaryMonthFilter();
        $this->loadPropertyUnitStats();
        $this->loadFinancialData();
        $this->loadMonthlyData();
    }

    private function loadPropertyUnitStats(): void
    {
        $today = Carbon::today();
        $activeLeaseConstraint = function ($query) use ($today) {
            $query->where('status', 'Active')
                ->where(function ($activeQuery) use ($today) {
                    $activeQuery->whereNull('move_out')
                        ->orWhereDate('move_out', '>', $today);
                })
                ->where(function ($activeQuery) use ($today) {
                    $activeQuery->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                });
        };

        $this->totalUnits = Unit::count();

        // Fully booked units: every bed in the unit has an active lease.
        $this->fullyBookedUnits = Unit::whereHas('beds')
            ->whereDoesntHave('beds', function ($bedQuery) use ($activeLeaseConstraint) {
                $bedQuery->whereDoesntHave('leases', $activeLeaseConstraint);
            })
            ->count();

        // Available units are partially occupied units:
        // at least one bed is actively leased and at least one bed is not actively leased.
        $this->availableUnits = Unit::whereHas('beds', function ($bedQuery) use ($activeLeaseConstraint) {
            $bedQuery->whereHas('leases', $activeLeaseConstraint);
        })->whereHas('beds', function ($bedQuery) use ($activeLeaseConstraint) {
            $bedQuery->whereDoesntHave('leases', $activeLeaseConstraint);
        })->count();

        // Vacant units: no bed in the unit has an active lease.
        $this->vacantUnits = Unit::whereDoesntHave('beds', function ($bedQuery) use ($activeLeaseConstraint) {
            $bedQuery->whereHas('leases', $activeLeaseConstraint);
        })->count();
    }

    public function updatedRentSummaryMonth(): void
    {
        $this->loadFinancialData();
        $this->dispatch('dashboard-refresh-charts');
    }

    private function initializeRentSummaryMonthFilter(): void
    {
        $now = Carbon::now();
        $options = [];

        // Limit filter options to months in the current year (current month + previous months).
        for ($month = $now->month; $month >= 1; $month--) {
            $monthDate = Carbon::create($now->year, $month, 1)->startOfMonth();
            $key = $monthDate->format('Y-m');
            $options[$key] = $monthDate->format('F Y');
        }

        $this->rentSummaryMonthOptions = $options;
        $this->rentSummaryMonth = $now->format('Y-m');
    }

    private function resolveRentSummaryMonth(): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', (string) $this->rentSummaryMonth)->startOfMonth();
        } catch (\Throwable $exception) {
            $this->rentSummaryMonth = Carbon::now()->format('Y-m');

            return Carbon::now()->startOfMonth();
        }
    }

    public function loadFinancialData()
    {
        $summaryMonth = $this->resolveRentSummaryMonth();
        $this->rentSummaryPeriodLabel = $summaryMonth->format('F Y');

        // Collected rent is based on the selected summary month.
        $this->totalRentCollected = Transaction::where('transaction_type', 'Credit')
            ->where('category', 'Rent Payment')
            ->whereYear('transaction_date', $summaryMonth->year)
            ->whereMonth('transaction_date', $summaryMonth->month)
            ->sum('amount');

        // on the off chance you still want to include credits from transactions,
        // keep them available for the income figure below

        // Calculate total uncollected rent for the selected billing month.
        $this->totalUncollectedRent = Billing::where('status', 'Unpaid')
            ->whereYear('billing_date', $summaryMonth->year)
            ->whereMonth('billing_date', $summaryMonth->month)
            ->sum('to_pay');

        // Total Income should show amount actually collected (same as collected rent)
        // additional uncollected rent is only used for the "target"/total figure.
        $this->totalIncome = $this->totalRentCollected;

        // If you still have real transaction data available, you could override
        // the above by uncommenting the following block:
        /*
        $incomeFromTransactions = Transaction::whereHas('billing.lease.bed.unit.property', function ($query) use ($user) {
            $query->where('owner_id', $user->user_id);
        })
            ->where('transaction_type', 'Credit')
            ->sum('amount');
        if ($incomeFromTransactions > 0) {
            $this->totalIncome = $incomeFromTransactions;
        }
        */

        // Revenue Current is simply what has already been collected
        $this->revenueCurrent = $this->totalRentCollected;

        // Revenue Target should reflect the grand total (collected + uncollected)
        $this->revenueTarget = $this->totalRentCollected + $this->totalUncollectedRent;

        // Calculate Total Expenses (maintenance costs for current month)
        $this->expensesCurrent = MaintenanceLog::sum('cost');

        // Expenses Target (set as 20% of revenue target or a fixed amount)
        $this->expensesTarget = $this->revenueTarget * 0.20;

        // Calculate ROI Current (Return on Investment)
        if ($this->expensesCurrent > 0) {
            $this->roiCurrent = (($this->revenueCurrent - $this->expensesCurrent) / $this->expensesCurrent) * 100;
        } else {
            $this->roiCurrent = 0;
        }

        // ROI Target (typical target is 30-50% ROI)
        $this->roiTarget = 35;

        // Calculate percentages
        $totalPotentialRent = $this->totalRentCollected + $this->totalUncollectedRent;

        // Prevent "Division by Zero" error if there is no rent data yet
        if ($totalPotentialRent > 0) {
            $this->rentCollectedPercentage = ($this->totalRentCollected / $totalPotentialRent) * 100;
            $this->uncollectedPercentage = ($this->totalUncollectedRent / $totalPotentialRent) * 100;
        } else {
            $this->rentCollectedPercentage = 0;
            $this->uncollectedPercentage = 0;
        }

        // For Income, let's compare it against your Revenue Target
        if ($this->revenueTarget > 0) {
            $this->incomePercentage = ($this->totalIncome / $this->revenueTarget) * 100;
        } else {
            $this->incomePercentage = 0;
        }

    }

    private function loadMonthlyData()
    {
        $year = Carbon::now()->year;

        // Initialize monthly arrays
        $this->monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->monthlyRevenue = array_fill(0, 12, 0);
        $this->monthlyExpenses = array_fill(0, 12, 0);
        $this->monthlyRentCollected = array_fill(0, 12, 0);

        // Get monthly revenue from credit transactions.
        $monthlyBillings = Transaction::where('transaction_type', 'Credit')
            ->where('category', 'Rent Payment')
            ->whereYear('transaction_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM transaction_date) as month, SUM(amount) as total')->groupBy('month')
            ->get();

        foreach ($monthlyBillings as $billing) {
            $this->monthlyRevenue[$billing->month - 1] = $billing->total;
            $this->monthlyRentCollected[$billing->month - 1] = $billing->total;
        }

        // Keep revenue as per-month totals (no cumulative carry-over).

        // Get monthly expenses (maintenance logs)
        $monthlyExpensesData = MaintenanceLog::whereYear('completion_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM completion_date) as month, SUM(cost) as total')
            ->groupBy('month')
            ->get();

        foreach ($monthlyExpensesData as $expense) {
            $this->monthlyExpenses[$expense->month - 1] = $expense->total;
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {

        return view('users.admin.owner.dashboard');
    }
}
