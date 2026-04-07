<?php

namespace App\Livewire\Layouts\Properties;

use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ContractsPanel extends Component
{
    use WithPagination;

    public string $activeTab = 'all';
    public string $search = '';
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;
    public ?string $selectedBuilding = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedYear(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBuilding(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function viewContract(int $leaseId, string $contractType = 'move-in'): void
    {
        $this->dispatch('open-landlord-contract-viewer', leaseId: $leaseId, contractType: $contractType);
    }

    #[On('signature-saved')]
    #[On('moveout-signature-saved')]
    public function refreshData(): void
    {
        // Re-render after a signature is saved
    }

    public function render()
    {
        $user = Auth::user();

        $baseQuery = Lease::whereHas('bed.unit.property', fn($q) => $q->where('owner_id', $user->user_id))
            ->with(['tenant', 'bed.unit.property']);

        // Apply search
        if ($this->search) {
            $search = $this->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->whereHas('tenant', fn($tq) => $tq->where('first_name', 'ilike', "%{$search}%")->orWhere('last_name', 'ilike', "%{$search}%"))
                  ->orWhereHas('bed.unit.property', fn($pq) => $pq->where('building_name', 'ilike', "%{$search}%"))
                  ->orWhereHas('bed.unit', fn($uq) => $uq->where('unit_number', 'ilike', "%{$search}%"));
            });
        }

        // Apply month filter
        if ($this->selectedMonth) {
            $baseQuery->whereMonth('start_date', $this->selectedMonth);
        }

        // Apply year filter
        if ($this->selectedYear) {
            $baseQuery->whereYear('start_date', $this->selectedYear);
        }

        // Apply building filter
        if ($this->selectedBuilding) {
            $baseQuery->whereHas('bed.unit.property', fn($q) => $q->where('building_name', $this->selectedBuilding));
        }

        // Tab counts (after search/month/building filters)
        $counts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where(fn($q) => $q->where('contract_status', 'pending_signatures')
                ->orWhere('contract_status', 'pending_tenant')
                ->orWhere('contract_status', 'pending_owner')
                ->orWhere('contract_status', 'pending_manager'))->count(),
            'signed' => (clone $baseQuery)->where('contract_status', 'executed')->count(),
            'draft' => (clone $baseQuery)->where(fn($q) => $q->whereNull('contract_status')->orWhere('contract_status', 'draft'))->count(),
        ];

        // Apply tab filter
        $query = clone $baseQuery;
        if ($this->activeTab === 'pending') {
            $query->where(fn($q) => $q->where('contract_status', 'pending_signatures')
                ->orWhere('contract_status', 'pending_tenant')
                ->orWhere('contract_status', 'pending_owner')
                ->orWhere('contract_status', 'pending_manager'));
        } elseif ($this->activeTab === 'signed') {
            $query->where('contract_status', 'executed');
        } elseif ($this->activeTab === 'draft') {
            $query->where(fn($q) => $q->whereNull('contract_status')->orWhere('contract_status', 'draft'));
        }

        // Month options
        $monthOptions = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthOptions[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        // Building options
        $buildingOptions = Property::where('owner_id', $user->user_id)
            ->pluck('building_name', 'building_name')
            ->toArray();

        // Search suggestions
        $suggestions = User::where('role', 'tenant')
            ->whereHas('leases.bed.unit.property', fn($q) => $q->where('owner_id', $user->user_id))
            ->get()
            ->map(fn($t) => $t->first_name . ' ' . $t->last_name)
            ->unique()
            ->values()
            ->toArray();

        $currentYear = (int) date('Y');
        $yearOptions = array_combine(
            range($currentYear, $currentYear - 4),
            range($currentYear, $currentYear - 4)
        );

        return view('livewire.layouts.properties.contracts-panel', [
            'leases' => $query->latest('start_date')->paginate(10),
            'counts' => $counts,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'buildingOptions' => $buildingOptions,
            'suggestions' => $suggestions,
        ]);
    }
}
