<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\BillingItem;
use App\Models\UtilityBill;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TenantUtilityHistory extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $selectedMonth = null;
    public $search = '';
    public $expandedRow = null;

    public function toggleRow($id)
    {
        $this->expandedRow = $this->expandedRow === $id ? null : $id;
    }

    public function setTab($tab) { $this->activeTab = $tab; $this->resetPage(); }
    public function updatedActiveTab() { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    private function baseQuery()
    {
        $query = BillingItem::whereIn('charge_type', ['electricity_share', 'water_share'])
            ->whereHas('billing.lease', function ($q) {
                $q->where('tenant_id', Auth::user()->user_id);
            })
            ->with(['billing.lease.bed.unit.property']);

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhereHas('billing.lease.bed.unit.property', function ($q) use ($search) {
                      $q->where('building_name', 'like', $search);
                  });
            });
        }

        return $query;
    }

    public function render()
    {
        $monthOptions = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $baseQuery = $this->baseQuery();

        $counts = [
            'all'         => (clone $baseQuery)->count(),
            'electricity' => (clone $baseQuery)->where('charge_type', 'electricity_share')->count(),
            'water'       => (clone $baseQuery)->where('charge_type', 'water_share')->count(),
        ];

        $query = clone $baseQuery;

        match ($this->activeTab) {
            'electricity' => $query->where('charge_type', 'electricity_share'),
            'water'       => $query->where('charge_type', 'water_share'),
            default       => null,
        };

        if ($this->selectedMonth) {
            $query->whereHas('billing', function ($q) {
                $q->whereMonth('billing_date', $this->selectedMonth);
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);

        // Load utility bill breakdown for the expanded row
        $expandedBill = null;
        if ($this->expandedRow) {
            $item = BillingItem::with('billing.lease.bed.unit')->find($this->expandedRow);
            if ($item) {
                $unit = $item->billing?->lease?->bed?->unit;
                $billingDate = $item->billing?->billing_date;
                $utilityType = $item->charge_type === 'electricity_share' ? 'electricity' : 'water';

                if ($unit) {
                    // Try exact month match first, then closest match by date
                    $query = UtilityBill::where('unit_id', $unit->unit_id)
                        ->where('utility_type', $utilityType);

                    if ($billingDate) {
                        $expandedBill = (clone $query)
                            ->whereMonth('billing_period', $billingDate->month)
                            ->whereYear('billing_period', $billingDate->year)
                            ->first();
                    }

                    // Fallback: find closest utility bill by per_tenant_amount matching
                    if (!$expandedBill) {
                        $expandedBill = $query
                            ->where('per_tenant_amount', $item->amount)
                            ->orderBy('billing_period', 'desc')
                            ->first();
                    }
                }
            }
        }

        return view('livewire.layouts.tenants.tenant-utility-history', [
            'items'        => $items,
            'counts'       => $counts,
            'monthOptions' => $monthOptions,
            'expandedBill' => $expandedBill,
        ]);
    }
}
