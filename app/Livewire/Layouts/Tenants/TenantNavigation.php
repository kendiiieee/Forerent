<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Lease;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class TenantNavigation extends Component
{
    public $tenants = [];
    public $allTenants = [];
    public $user;
    public $activeTenantId = null;
    public ?int $selectedBuildingId = null;
    public $activeTab = 'current';
    public $sortOrder = 'newest';
    public $search = '';
    public $counts = [
        'current'     => 0,
        'transferred' => 0,
        'moved_out'   => 0,
    ];

    public function mount($tenants = null): void
    {
        $this->user = Auth::user();

        $firstProperty = \App\Models\Property::whereHas('units', function ($query) {
            $query->where('manager_id', Auth::id());
        })->first();

        if ($firstProperty) {
            $this->selectedBuildingId = $firstProperty->property_id;
            $this->loadTenants();
            $this->loadCounts();
        }
    }

    #[On('tenant-property-selected')]
    public function onPropertySelected(int $id): void
    {
        $this->switchBuilding($id);
    }

    #[On('buildingSelected')]
    public function onBuildingSelected($buildingId): void
    {
        $this->switchBuilding((int) $buildingId);
    }

    private function switchBuilding(int $id): void
    {
        if ($this->selectedBuildingId === $id && $this->activeTab === 'current') {
            return;
        }

        $this->selectedBuildingId = $id;
        $this->activeTab = 'current';
        $this->activeTenantId = null;
        $this->search = '';
        $this->loadTenants();
        $this->loadCounts();
    }

    public function setTab($tab): void
    {
        $this->activeTab = $tab;
        $this->activeTenantId = null;
        $this->search = '';
        $this->loadTenants();
    }

    public function updatedSortOrder(): void
    {
        $this->tenants = $this->applySorting($this->tenants);
    }

    public function updatedSearch(): void
    {
        $this->activeTenantId = null;
        $this->loadTenants();
    }

    #[On('refresh-tenant-list')]
    public function refreshTenantList(): void
    {
        if ($this->selectedBuildingId) {
            $this->loadTenants();
            $this->loadCounts();
        } else {
            $this->tenants = [];
            $this->allTenants = [];
        }
    }

    #[On('tenantActivated')]
    public function activateTenant(int $tenantId): void
    {
        $this->activeTenantId = $tenantId;
    }

    public function selectTenant(int $tenantId): void
    {
        $this->activeTenantId = $tenantId;
        $this->dispatch('tenantSelected',
            tenantId: $tenantId,
            tab: $this->activeTab,
            buildingId: $this->selectedBuildingId
        );
    }

    private function loadTenants(): void
    {
        $raw = match ($this->activeTab) {
            'transferred' => $this->loadTransferredTenants(),
            'moved_out'   => $this->loadMovedOutTenants(),
            default       => $this->loadCurrentTenants(),
        };

        $this->allTenants = $raw;
        $this->tenants = $this->applySorting($this->applySearch($raw));
    }

    private function loadCurrentTenants(): array
    {
        return Unit::where('manager_id', Auth::id())
            ->where('property_id', $this->selectedBuildingId)
            ->with([
                'beds.leases' => fn($q) => $q->where('status', 'Active')->with([
                    'tenant' => fn($q) => $q->where('role', 'tenant'),
                    'billings' => fn($q) => $q
                        ->whereMonth('billing_date', now()->month)
                        ->whereYear('billing_date', now()->year)
                        ->latest()
                        ->limit(1)
                ])
            ])
            ->get()
            ->flatMap(function ($unit) {
                return $unit->beds->flatMap(function ($bed) use ($unit) {
                    return $bed->leases
                        ->filter(fn($lease) => $lease->tenant !== null)
                        ->map(fn($lease) => [
                            'id'             => $lease->tenant->user_id,
                            'first_name'     => $lease->tenant->first_name,
                            'last_name'      => $lease->tenant->last_name,
                            'unit'           => $unit->unit_number,
                            'bed_number'     => $bed->bed_number,
                            'payment_status' => $lease->billings->first()?->status ?? 'No billing',
                            'next_billing'   => $lease->billings->first()?->next_billing ?? null,
                            'created_at'     => $lease->created_at,
                        ]);
                });
            })
            ->unique('id')
            ->values()
            ->toArray();
    }

    private function getUnitIds()
    {
        return Unit::where('manager_id', Auth::id())
            ->where('property_id', $this->selectedBuildingId)
            ->pluck('unit_id');
    }

    private function loadTransferredTenants(): array
    {
        $data = $this->loadExpiredSplit();
        return $data['transferred'];
    }

    private function loadMovedOutTenants(): array
    {
        $data = $this->loadExpiredSplit();
        return $data['moved_out'];
    }

    private function loadExpiredSplit(): array
    {
        $unitIds = $this->getUnitIds();

        if ($unitIds->isEmpty()) {
            return ['transferred' => [], 'moved_out' => []];
        }

        $expiredLeases = Lease::where('leases.status', 'Expired')
            ->join('beds', 'beds.bed_id', '=', 'leases.bed_id')
            ->whereIn('beds.unit_id', $unitIds)
            ->with([
                'tenant' => fn($q) => $q->where('role', 'tenant'),
                'bed.unit',
            ])
            ->select('leases.*')
            ->orderBy('leases.end_date', 'desc')
            ->get()
            ->filter(fn($lease) => $lease->tenant !== null);

        if ($expiredLeases->isEmpty()) {
            return ['transferred' => [], 'moved_out' => []];
        }

        $expiredTenantIds = $expiredLeases->pluck('tenant_id')->unique();
        $activeTenantIds = Lease::where('leases.status', 'Active')
            ->whereIn('tenant_id', $expiredTenantIds)
            ->pluck('tenant_id')
            ->flip();

        $transferred = [];
        $movedOut = [];
        $seen = [];

        foreach ($expiredLeases as $lease) {
            $tid = $lease->tenant_id;
            if (isset($seen[$tid])) continue;
            $seen[$tid] = true;

            $entry = [
                'id'             => $lease->tenant->user_id,
                'first_name'     => $lease->tenant->first_name,
                'last_name'      => $lease->tenant->last_name,
                'unit'           => $lease->bed->unit->unit_number ?? 'N/A',
                'bed_number'     => $lease->bed->bed_number ?? 'N/A',
                'payment_status' => $activeTenantIds->has($tid) ? 'Transferred' : 'Moved Out',
                'next_billing'   => $lease->end_date,
                'created_at'     => $lease->created_at,
            ];

            if ($activeTenantIds->has($tid)) {
                $transferred[] = $entry;
            } else {
                $movedOut[] = $entry;
            }
        }

        return ['transferred' => $transferred, 'moved_out' => $movedOut];
    }

    private function loadCounts(): void
    {
        $unitIds = $this->getUnitIds();

        if ($unitIds->isEmpty()) {
            $this->counts = ['current' => 0, 'transferred' => 0, 'moved_out' => 0];
            return;
        }

        $currentCount = Lease::where('leases.status', 'Active')
            ->whereIn('bed_id', function ($q) use ($unitIds) {
                $q->select('bed_id')->from('beds')->whereIn('unit_id', $unitIds);
            })
            ->whereIn('tenant_id', function ($q) {
                $q->select('user_id')->from('users')->where('role', 'tenant');
            })
            ->distinct()
            ->count('tenant_id');

        $expiredTenantIds = Lease::where('leases.status', 'Expired')
            ->whereIn('bed_id', function ($q) use ($unitIds) {
                $q->select('bed_id')->from('beds')->whereIn('unit_id', $unitIds);
            })
            ->whereIn('tenant_id', function ($q) {
                $q->select('user_id')->from('users')->where('role', 'tenant');
            })
            ->distinct()
            ->pluck('tenant_id');

        $transferredCount = 0;
        $movedOutCount = 0;

        if ($expiredTenantIds->isNotEmpty()) {
            $transferredCount = Lease::where('leases.status', 'Active')
                ->whereIn('tenant_id', $expiredTenantIds)
                ->distinct()
                ->count('tenant_id');
            $movedOutCount = $expiredTenantIds->unique()->count() - $transferredCount;
        }

        $this->counts = [
            'current'     => $currentCount,
            'transferred' => $transferredCount,
            'moved_out'   => max(0, $movedOutCount),
        ];
    }

    private function applySearch(array $tenants): array
    {
        if (empty($this->search)) {
            return $tenants;
        }

        $term = strtolower($this->search);

        return array_values(array_filter($tenants, function ($t) use ($term) {
            $name = strtolower(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''));
            $unit = strtolower('unit ' . ($t['unit'] ?? ''));
            $unitNum = strtolower($t['unit'] ?? '');
            $bed = strtolower('bed ' . ($t['bed_number'] ?? ''));
            $bedNum = strtolower($t['bed_number'] ?? '');

            return str_contains($name, $term)
                || str_contains($unit, $term)
                || str_contains($unitNum, $term)
                || str_contains($bed, $term)
                || str_contains($bedNum, $term);
        }));
    }

    private function applySorting(array $tenants): array
    {
        $direction = $this->sortOrder === 'newest' ? -1 : 1;
        usort($tenants, function ($a, $b) use ($direction) {
            $aDate = strtotime($a['next_billing'] ?? $a['created_at'] ?? '1970-01-01');
            $bDate = strtotime($b['next_billing'] ?? $b['created_at'] ?? '1970-01-01');
            return ($aDate <=> $bDate) * $direction;
        });

        return array_values($tenants);
    }

    public function render()
    {
        $suggestions = collect($this->allTenants)
            ->flatMap(fn($t) => [
                ($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''),
                'Unit ' . ($t['unit'] ?? ''),
                'Bed ' . ($t['bed_number'] ?? ''),
            ])
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.tenants.tenant-navigation', [
            'suggestions' => $suggestions,
        ]);
    }
}
