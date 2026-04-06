<?php

namespace App\Livewire\Layouts\Financials;

use App\Models\Property;
use App\Models\UtilityBill;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UtilityBillTable extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $selectedMonth = null;
    public $selectedBuilding = null;
    public $search = '';

    public function setTab($tab) { $this->activeTab = $tab; $this->resetPage(); }
    public function updatedActiveTab() { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->resetPage(); }
    public function updatedSelectedBuilding() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    private function baseQuery()
    {
        $query = UtilityBill::whereHas('unit', function ($q) {
                $q->where('manager_id', Auth::id());
            })
            ->with('unit.property');

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->whereHas('unit.property', function ($q) use ($search) {
                $q->where('building_name', 'like', $search);
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

        $buildingOptions = [];
        try {
            $buildingOptions = Property::whereHas('units', function ($q) {
                $q->where('manager_id', Auth::id());
            })->distinct()->pluck('building_name', 'building_name')->toArray();
        } catch (\Exception $e) {
            $buildingOptions = [];
        }

        $baseQuery = $this->baseQuery();

        $counts = [
            'all'         => (clone $baseQuery)->count(),
            'electricity' => (clone $baseQuery)->where('utility_type', 'electricity')->count(),
            'water'       => (clone $baseQuery)->where('utility_type', 'water')->count(),
        ];

        $query = clone $baseQuery;

        match ($this->activeTab) {
            'electricity' => $query->where('utility_type', 'electricity'),
            'water'       => $query->where('utility_type', 'water'),
            default       => null,
        };

        if ($this->selectedMonth) {
            $query->whereMonth('billing_period', $this->selectedMonth);
        }

        if ($this->selectedBuilding) {
            $query->whereHas('unit.property', function ($q) {
                $q->where('building_name', $this->selectedBuilding);
            });
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(10);

        $suggestions = UtilityBill::whereHas('unit', function ($q) {
                $q->where('manager_id', Auth::id());
            })
            ->with('unit.property')
            ->get()
            ->pluck('unit.property.building_name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.financials.utility-bill-table', [
            'bills'            => $bills,
            'counts'           => $counts,
            'monthOptions'     => $monthOptions,
            'buildingOptions'  => $buildingOptions,
            'suggestions'      => $suggestions,
        ]);
    }
}
