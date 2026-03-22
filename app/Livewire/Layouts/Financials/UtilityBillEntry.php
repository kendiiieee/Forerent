<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\UtilityBill;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UtilityBillEntry extends Component
{
    use WithPagination;

    public $selectedUnit = null;
    public $utilityType = 'electricity';
    public $billingPeriod = '';
    public $totalAmount = '';
    public $tenantCount = 0;
    public $perTenantAmount = 0;
    public $showConfirmation = false;

    protected $rules = [
        'selectedUnit'  => 'required|exists:units,unit_id',
        'utilityType'   => 'required|in:electricity,water',
        'billingPeriod' => 'required|date',
        'totalAmount'   => 'required|numeric|min:1',
    ];

    public function mount()
    {
        $this->billingPeriod = Carbon::now()->startOfMonth()->format('Y-m');
    }

    public function updatedSelectedUnit()
    {
        $this->calculateSplit();
    }

    public function updatedTotalAmount()
    {
        $this->calculateSplit();
    }

    public function calculateSplit()
    {
        if (!$this->selectedUnit || !$this->totalAmount) {
            $this->tenantCount = 0;
            $this->perTenantAmount = 0;
            return;
        }

        // Count active tenants in the selected unit
        $this->tenantCount = Lease::where('status', 'Active')
            ->whereHas('bed', function ($q) {
                $q->where('unit_id', $this->selectedUnit);
            })
            ->count();

        if ($this->tenantCount > 0 && is_numeric($this->totalAmount) && $this->totalAmount > 0) {
            $this->perTenantAmount = round((float)$this->totalAmount / $this->tenantCount, 2);
        } else {
            $this->perTenantAmount = 0;
        }
    }

    public function confirmSave()
    {
        $this->validate();
        $this->calculateSplit();

        if ($this->tenantCount === 0) {
            $this->dispatch('show-toast', ['message' => 'No active tenants in this unit.', 'type' => 'error']);
            return;
        }

        $this->showConfirmation = true;
    }

    public function save()
    {
        $this->validate();
        $this->calculateSplit();

        if ($this->tenantCount === 0) {
            $this->dispatch('show-toast', ['message' => 'No active tenants in this unit.', 'type' => 'error']);
            return;
        }

        $periodDate = Carbon::parse($this->billingPeriod . '-01')->startOfMonth();

        DB::transaction(function () use ($periodDate) {
            // Create UtilityBill record
            UtilityBill::create([
                'unit_id'           => $this->selectedUnit,
                'utility_type'      => $this->utilityType,
                'billing_period'    => $periodDate->format('Y-m-d'),
                'total_amount'      => $this->totalAmount,
                'tenant_count'      => $this->tenantCount,
                'per_tenant_amount' => $this->perTenantAmount,
                'entered_by'        => Auth::id(),
            ]);

            // Find active leases for this unit and add billing items
            $leases = Lease::where('status', 'Active')
                ->whereHas('bed', function ($q) {
                    $q->where('unit_id', $this->selectedUnit);
                })
                ->get();

            $chargeType = $this->utilityType === 'electricity' ? 'electricity_share' : 'water_share';
            $description = $this->utilityType === 'electricity'
                ? "Electricity Share (Meralco ₱" . number_format($this->totalAmount, 2) . " ÷ {$this->tenantCount} tenants)"
                : "Water Share (₱" . number_format($this->totalAmount, 2) . " ÷ {$this->tenantCount} tenants)";

            foreach ($leases as $lease) {
                // Find the current month's billing for this lease
                $billing = Billing::where('lease_id', $lease->lease_id)
                    ->where('billing_type', 'monthly')
                    ->whereMonth('billing_date', $periodDate->month)
                    ->whereYear('billing_date', $periodDate->year)
                    ->first();

                if ($billing) {
                    // Check if utility item already exists for this billing
                    $existing = BillingItem::where('billing_id', $billing->billing_id)
                        ->where('charge_type', $chargeType)
                        ->first();

                    if ($existing) {
                        // Update existing
                        $oldAmount = $existing->amount;
                        $existing->update([
                            'amount'      => $this->perTenantAmount,
                            'description' => $description,
                        ]);
                        // Update billing total
                        $billing->update([
                            'to_pay' => $billing->to_pay - $oldAmount + $this->perTenantAmount,
                            'amount' => $billing->amount - $oldAmount + $this->perTenantAmount,
                        ]);
                    } else {
                        // Create new billing item
                        BillingItem::create([
                            'billing_id'      => $billing->billing_id,
                            'charge_category' => 'recurring',
                            'charge_type'     => $chargeType,
                            'description'     => $description,
                            'amount'          => $this->perTenantAmount,
                        ]);

                        // Update billing total
                        $billing->update([
                            'to_pay' => $billing->to_pay + $this->perTenantAmount,
                            'amount' => $billing->amount + $this->perTenantAmount,
                        ]);
                    }
                }
            }
        });

        $this->dispatch('show-toast', ['message' => ucfirst($this->utilityType) . ' bill split and applied to ' . $this->tenantCount . ' tenants.']);
        $this->reset(['selectedUnit', 'totalAmount', 'tenantCount', 'perTenantAmount', 'showConfirmation']);
        $this->billingPeriod = Carbon::now()->startOfMonth()->format('Y-m');
    }

    public function render()
    {
        // Get units managed by current user
        $units = Unit::where('manager_id', Auth::id())
            ->with('property')
            ->get()
            ->map(fn ($unit) => [
                'id'    => $unit->unit_id,
                'label' => $unit->property->building_name . ' — Unit ' . $unit->unit_number,
            ]);

        // Recent utility bills
        $recentBills = UtilityBill::whereHas('unit', function ($q) {
                $q->where('manager_id', Auth::id());
            })
            ->with('unit.property')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.layouts.financials.utility-bill-entry', [
            'units'       => $units,
            'recentBills' => $recentBills,
        ]);
    }
}
