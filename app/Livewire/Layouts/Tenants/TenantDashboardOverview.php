<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantDashboardOverview extends Component
{
    // Payment & Billing
    public $currentBilling = null;
    public $amountDue = 0;
    public $dueDate = null;
    public $daysUntilDue = 0;
    public $paymentStatus = 'No Billing';
    public $outstandingBalance = 0;
    public $nextPaymentDate = null;
    public $billingItems = [];

    // Utility Breakdown
    public $electricityShare = 0;
    public $electricityTotal = 0;
    public $waterShare = 0;
    public $waterTotal = 0;
    public $tenantCount = 0;
    public $billingPeriod = '';

    // Deposit & Fees
    public $securityDeposit = 0;
    public $advanceAmount = 0;
    public $activePenalties = [];
    public $totalPenalties = 0;

    // Lease & Contract
    public $lease = null;
    public $leaseStatus = 'N/A';
    public $leaseEndDate = null;
    public $daysUntilExpiry = 0;
    public $leaseTerm = 0;
    public $contractRate = 0;
    public $isShortTerm = false;
    public $autoRenew = false;

    // Move-In / Move-Out
    public $moveInDate = null;
    public $moveOutDate = null;

    // Requests & Compliance
    public $openMaintenanceCount = 0;
    public $pendingMaintenanceCount = 0;
    public $ongoingMaintenanceCount = 0;
    public $recentRequests = [];

    public function mount()
    {
        $user = Auth::user();
        $this->lease = Lease::with(['bed.unit.property', 'billings.items'])
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->latest()
            ->first();

        if ($this->lease) {
            $this->loadBillingData();
            $this->loadUtilityData();
            $this->loadDepositData();
            $this->loadLeaseData();
            $this->loadMoveData();
            $this->loadMaintenanceData();
        }
    }

    protected function loadBillingData()
    {
        // Current/latest billing
        $this->currentBilling = Billing::with('items')
            ->where('lease_id', $this->lease->lease_id)
            ->orderBy('billing_date', 'desc')
            ->first();

        if ($this->currentBilling) {
            $this->amountDue = $this->currentBilling->to_pay;
            $this->dueDate = $this->currentBilling->due_date;
            $this->paymentStatus = $this->currentBilling->status;
            $this->billingItems = $this->currentBilling->items ?? collect();

            if ($this->dueDate) {
                $this->daysUntilDue = Carbon::now()->startOfDay()->diffInDays(
                    Carbon::parse($this->dueDate)->startOfDay(),
                    false
                );
            }
        }

        // Outstanding balance from previous months
        $this->outstandingBalance = Billing::where('lease_id', $this->lease->lease_id)
            ->whereIn('status', ['Unpaid', 'Overdue'])
            ->where('billing_id', '!=', optional($this->currentBilling)->billing_id)
            ->sum('to_pay');

        // Next payment date
        if ($this->currentBilling && $this->currentBilling->next_billing) {
            $this->nextPaymentDate = $this->currentBilling->next_billing;
        }
    }

    protected function loadUtilityData()
    {
        $unit = $this->lease->bed->unit ?? null;
        if (!$unit) return;

        // Current month utilities
        $currentPeriod = Carbon::now()->startOfMonth()->format('Y-m-d');

        $electricity = UtilityBill::where('unit_id', $unit->unit_id)
            ->where('utility_type', 'electricity')
            ->orderBy('billing_period', 'desc')
            ->first();

        $water = UtilityBill::where('unit_id', $unit->unit_id)
            ->where('utility_type', 'water')
            ->orderBy('billing_period', 'desc')
            ->first();

        if ($electricity) {
            $this->electricityShare = $electricity->per_tenant_amount;
            $this->electricityTotal = $electricity->total_amount;
            $this->tenantCount = $electricity->tenant_count;
            $this->billingPeriod = Carbon::parse($electricity->billing_period)->format('M Y');
        }

        if ($water) {
            $this->waterShare = $water->per_tenant_amount;
            $this->waterTotal = $water->total_amount;
            if (!$this->tenantCount && $water->tenant_count) {
                $this->tenantCount = $water->tenant_count;
            }
        }

    }

    protected function loadDepositData()
    {
        $this->securityDeposit = $this->lease->security_deposit ?? 0;
        $this->advanceAmount = $this->lease->advance_amount ?? 0;

        // Get active penalties from billing items
        $this->activePenalties = BillingItem::whereHas('billing', function ($q) {
                $q->where('lease_id', $this->lease->lease_id);
            })
            ->where('charge_category', 'conditional')
            ->whereIn('charge_type', ['late_fee', 'violation_fee', 'short_term_premium'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $this->totalPenalties = $this->activePenalties->sum('amount');
    }

    protected function loadLeaseData()
    {
        $this->leaseStatus = $this->lease->status;
        $this->leaseEndDate = $this->lease->end_date;
        $this->leaseTerm = $this->lease->term;
        $this->contractRate = $this->lease->contract_rate;
        $this->autoRenew = $this->lease->auto_renew;
        $this->isShortTerm = $this->lease->term <= 3;

        if ($this->leaseEndDate) {
            $this->daysUntilExpiry = Carbon::now()->startOfDay()->diffInDays(
                Carbon::parse($this->leaseEndDate)->startOfDay(),
                false
            );
        }
    }

    protected function loadMoveData()
    {
        $this->moveInDate = $this->lease->move_in;
        $this->moveOutDate = $this->lease->move_out;
    }

    protected function loadMaintenanceData()
    {
        $leaseIds = Auth::user()->leases()->pluck('lease_id');

        $this->openMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->count();

        $this->pendingMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->where('status', 'Pending')
            ->count();

        $this->ongoingMaintenanceCount = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->where('status', 'Ongoing')
            ->count();

        $this->recentRequests = MaintenanceRequest::whereIn('lease_id', $leaseIds)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.layouts.tenants.tenant-dashboard-overview');
    }
}
