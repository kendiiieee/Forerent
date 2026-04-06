<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;
use App\Models\UtilityBill;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UtilityBillEntry extends Component
{
    use WithPagination, \App\Livewire\Concerns\WithNotifications;

    public $isOpen = false;
    public $selectedBuilding = null;
    public $selectedUnit = null;
    public $utilityType = 'electricity';
    public $billingPeriod = '';
    public $totalAmount = '';
    public $tenantCount = 0;
    public $perTenantAmount = 0;

    protected $rules = [
        'selectedUnit'  => 'required|exists:units,unit_id',
        'utilityType'   => 'required|in:electricity,water',
        'billingPeriod' => 'required|date',
        'totalAmount'   => 'required|numeric|min:1|max:999999.99',
    ];

    protected $messages = [
        'totalAmount.required' => 'Please enter the bill amount.',
        'totalAmount.min'      => 'Amount must be at least ₱1.00.',
        'totalAmount.max'      => 'Amount cannot exceed ₱999,999.99.',
        'totalAmount.numeric'  => 'Please enter a valid number.',
        'selectedUnit.required' => 'Please select a unit.',
        'billingPeriod.required' => 'Please select a billing period.',
    ];

    public function mount()
    {
        $this->billingPeriod = Carbon::now()->startOfMonth()->format('Y-m');
    }

    #[On('open-utility-bill-modal')]
    public function open()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function close()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->isOpen = false;
    }

    public function resetForm()
    {
        $this->reset(['selectedBuilding', 'selectedUnit', 'totalAmount', 'tenantCount', 'perTenantAmount']);
        $this->utilityType = 'electricity';
        $this->billingPeriod = Carbon::now()->startOfMonth()->format('Y-m');
    }

    public function updatedSelectedBuilding()
    {
        $this->selectedUnit = null;
        $this->tenantCount = 0;
        $this->perTenantAmount = 0;
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
        if (!$this->selectedUnit) {
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
            $this->notifyError('No Active Tenants', 'Cannot split utility bill for this unit.');
            return;
        }

        $this->dispatch('open-modal', 'confirm-utility-split');
    }

    public function save()
    {
        $this->validate();
        $this->calculateSplit();

        if ($this->tenantCount === 0) {
            $this->notifyError('No Active Tenants', 'Cannot split utility bill for this unit.');
            return;
        }

        // Get unit label for toast before resetting
        $unit = Unit::with('property')->find($this->selectedUnit);
        $unitLabel = $unit ? $unit->property->building_name . ' — Unit ' . $unit->unit_number : 'Unknown';
        $utilityLabel = ucfirst($this->utilityType);
        $amountLabel = '₱' . number_format((float)$this->totalAmount, 2);

        $periodDate = Carbon::parse($this->billingPeriod . '-01')->startOfMonth();

        try {
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

        $this->dispatch('close-modal', 'confirm-utility-split');
        $this->notifySuccess(
            'Utility Bill Applied',
            "{$utilityLabel} bill of {$amountLabel} for {$unitLabel} split among {$this->tenantCount} tenant(s)."
        );
        $this->resetForm();
        $this->isOpen = false;
        } catch (\Exception $e) {
            $this->dispatch('close-modal', 'confirm-utility-split');
            $this->notifyError(
                'Utility Bill Failed',
                "Failed to apply {$utilityLabel} bill of {$amountLabel} for {$unitLabel}."
            );
        }
    }

    public function render()
    {
        // Get buildings that have units managed by current user with active tenants
        $buildings = Property::whereHas('units', function ($q) {
                $q->where('manager_id', Auth::id())
                  ->whereHas('beds.leases', fn ($l) => $l->where('status', 'Active'));
            })
            ->get()
            ->map(fn ($p) => [
                'id'    => $p->property_id,
                'label' => $p->building_name,
            ]);

        // Get units filtered by selected building, only those with active tenants
        $units = collect();
        if ($this->selectedBuilding) {
            $units = Unit::where('manager_id', Auth::id())
                ->where('property_id', $this->selectedBuilding)
                ->whereHas('beds.leases', fn ($q) => $q->where('status', 'Active'))
                ->with('property')
                ->get()
                ->map(fn ($unit) => [
                    'id'    => $unit->unit_id,
                    'label' => 'Unit ' . $unit->unit_number,
                ]);
        }

        return view('livewire.layouts.financials.utility-bill-entry', [
            'buildings' => $buildings,
            'units'     => $units,
        ]);
    }
}
