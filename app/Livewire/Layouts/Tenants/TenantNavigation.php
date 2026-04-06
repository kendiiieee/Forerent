<?php

namespace App\Livewire\Layouts\Tenants;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Billing;
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
    public $selectedBuildingName = null;
    public $buildingOptions = [];
    public $activeTab = 'current';
    public $sortOrder = 'newest';
    public $search = '';
    public $counts = [
        'current'     => 0,
        'moving_out'  => 0,
        'transferred' => 0,
        'moved_out'   => 0,
    ];

    public function mount($tenants = null): void
    {
        $this->user = Auth::user();
        $this->loadBuildingOptions();
        $this->loadTenants();
        $this->loadCounts();

        $this->autoSelectFirst();
    }

    private function autoSelectFirst(): void
    {
        if (!empty($this->tenants)) {
            $this->selectTenant($this->tenants[0]['id']);
        }
    }

    public function selectBuilding($id = null): void
    {
        $id = $id ? (int) $id : null;

        if ($this->selectedBuildingId === $id) {
            return;
        }

        $this->selectedBuildingId = $id;
        $this->selectedBuildingName = $id ? ($this->buildingOptions[$id] ?? null) : null;
        $this->activeTab = 'current';
        $this->activeTenantId = null;
        $this->search = '';
        $this->loadTenants();
        $this->loadCounts();
        $this->autoSelectFirst();
    }

    private function loadBuildingOptions(): void
    {
        $this->buildingOptions = Property::whereHas('units', function ($q) {
            $q->where('manager_id', Auth::id());
        })->orderBy('property_id')
            ->pluck('building_name', 'property_id')
            ->toArray();
    }

    public function setTab($tab): void
    {
        $this->activeTab = $tab;
        $this->activeTenantId = null;
        $this->search = '';
        $this->loadTenants();
        $this->autoSelectFirst();
    }

    public function updatedSortOrder(): void
    {
        $this->tenants = $this->applySorting($this->tenants);
    }

    public function updatedSearch(): void
    {
        $this->activeTenantId = null;
        $this->loadTenants();
        $this->autoSelectFirst();
    }

    #[On('refresh-tenant-list')]
    public function refreshTenantList(): void
    {
        $this->loadTenants();
        $this->loadCounts();
    }

    #[On('tenantActivated')]
    public function activateTenant(int $tenantId): void
    {
        $this->activeTenantId = $tenantId;
    }

    public function selectTenant(int $tenantId): void
    {
        $this->activeTenantId = $tenantId;
        $this->dispatch(
            'tenantSelected',
            tenantId: $tenantId,
            tab: $this->activeTab,
            buildingId: $this->selectedBuildingId
        );
    }

    private function loadTenants(): void
    {
        $raw = match ($this->activeTab) {
            'moving_out'  => $this->loadMovingOutTenants(),
            'transferred' => $this->loadTransferredTenants(),
            'moved_out'   => $this->loadMovedOutTenants(),
            default       => $this->loadCurrentTenants(),
        };

        $this->allTenants = $raw;
        $this->tenants = $this->applySorting($this->applySearch($raw));
    }

    private function loadCurrentTenants(): array
    {
        $unitIds = $this->getUnitIds();

        if ($unitIds->isEmpty()) {
            return [];
        }

        $leases = Lease::where('leases.status', 'Active')
            ->join('beds', 'beds.bed_id', '=', 'leases.bed_id')
            ->whereIn('beds.unit_id', $unitIds)
            ->with([
                'tenant' => fn($q) => $q->where('role', 'tenant'),
                'bed.unit',
            ])
            ->select('leases.*')
            ->get()
            ->filter(fn($lease) => $lease->tenant !== null);

        if ($leases->isEmpty()) {
            return [];
        }

        $tenantIds = $leases->pluck('tenant_id');

        // Get ALL lease IDs ever held by these tenants (not just active ones)
        $tenantLeaseMap = Lease::whereIn('tenant_id', $tenantIds)
            ->get(['lease_id', 'tenant_id'])
            ->groupBy('tenant_id');

        $allLeaseIds = $tenantLeaseMap->flatten()->pluck('lease_id');

        // Get all billings for those leases
        $billingsByLease = Billing::whereIn('lease_id', $allLeaseIds)
            ->get()
            ->groupBy('lease_id');

        // Build tenant_id -> priority billing map (overdue first, else latest)
        $latestBillingByTenant = $tenantLeaseMap->map(function ($tenantLeases) use ($billingsByLease) {
            $allBillings = $tenantLeases
                ->flatMap(fn($l) => $billingsByLease->get($l->lease_id, collect()));

            return $allBillings->firstWhere('status', 'Overdue')
                ?? $allBillings->sortByDesc('billing_date')->first();
        });

        return $leases
            ->unique('tenant_id')
            ->map(function ($lease) use ($latestBillingByTenant) {
                $latestBilling = $latestBillingByTenant->get($lease->tenant_id);

                return [
                    'id'             => $lease->tenant->user_id,
                    'first_name'     => $lease->tenant->first_name,
                    'last_name'      => $lease->tenant->last_name,
                    'unit'           => $lease->bed->unit->unit_number ?? 'N/A',
                    'bed_number'     => $lease->bed->bed_number ?? 'N/A',
                    'payment_status' => $latestBilling?->status ?? 'No billing',
                    'next_billing'   => $latestBilling?->billing_date ?? null,
                    'created_at'     => $lease->created_at,
                ];
            })
            ->values()
            ->toArray();
    }

    private function loadMovingOutTenants(): array
    {
        $unitIds = $this->getUnitIds();

        if ($unitIds->isEmpty()) {
            return [];
        }

        $leases = Lease::where('leases.status', 'Active')
            ->whereNotNull('leases.move_out_initiated_at')
            ->join('beds', 'beds.bed_id', '=', 'leases.bed_id')
            ->whereIn('beds.unit_id', $unitIds)
            ->with([
                'tenant' => fn($q) => $q->where('role', 'tenant'),
                'bed.unit',
            ])
            ->select('leases.*')
            ->get()
            ->filter(fn($lease) => $lease->tenant !== null);

        if ($leases->isEmpty()) {
            return [];
        }

        return $leases
            ->unique('tenant_id')
            ->map(function ($lease) {
                return [
                    'id'             => $lease->tenant->user_id,
                    'first_name'     => $lease->tenant->first_name,
                    'last_name'      => $lease->tenant->last_name,
                    'unit'           => $lease->bed->unit->unit_number ?? 'N/A',
                    'bed_number'     => $lease->bed->bed_number ?? 'N/A',
                    'payment_status' => 'Moving Out',
                    'next_billing'   => $lease->move_out_initiated_at,
                    'created_at'     => $lease->created_at,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUnitIds()
    {
        $query = Unit::where('manager_id', Auth::id());

        if ($this->selectedBuildingId !== null) {
            $query->where('property_id', $this->selectedBuildingId);
        }

        return $query->pluck('unit_id');
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

        $activeLeases = Lease::where('leases.status', 'Active')
            ->whereIn('tenant_id', $expiredTenantIds)
            ->get(['tenant_id', 'bed_id'])
            ->keyBy('tenant_id');

        $transferred = [];
        $movedOut    = [];
        $seen        = [];

        foreach ($expiredLeases as $lease) {
            $tid = $lease->tenant_id;
            if (isset($seen[$tid])) continue;
            $seen[$tid] = true;

            $activeLease = $activeLeases->get($tid);

            $isTransferred = $activeLease && $activeLease->bed_id !== $lease->bed_id;
            $isMovedOut    = !$activeLease;

            $entry = [
                'id'             => $lease->tenant->user_id,
                'first_name'     => $lease->tenant->first_name,
                'last_name'      => $lease->tenant->last_name,
                'unit'           => $lease->bed->unit->unit_number ?? 'N/A',
                'bed_number'     => $lease->bed->bed_number ?? 'N/A',
                'payment_status' => $isTransferred ? 'Transferred' : 'Moved Out',
                'next_billing'   => $lease->end_date,
                'created_at'     => $lease->created_at,
            ];

            if ($isTransferred) {
                $transferred[] = $entry;
            } elseif ($isMovedOut) {
                $movedOut[] = $entry;
            }
        }

        return ['transferred' => $transferred, 'moved_out' => $movedOut];
    }

    private function loadCounts(): void
    {
        $unitIds = $this->getUnitIds();

        if ($unitIds->isEmpty()) {
            $this->counts = ['current' => 0, 'moving_out' => 0, 'transferred' => 0, 'moved_out' => 0];
            return;
        }

        $movingOutCount = Lease::where('leases.status', 'Active')
            ->whereNotNull('move_out_initiated_at')
            ->whereIn('bed_id', function ($q) use ($unitIds) {
                $q->select('bed_id')->from('beds')->whereIn('unit_id', $unitIds);
            })
            ->whereIn('tenant_id', function ($q) {
                $q->select('user_id')->from('users')->where('role', 'tenant');
            })
            ->distinct()
            ->count('tenant_id');

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
        $movedOutCount    = 0;

        if ($expiredTenantIds->isNotEmpty()) {
            $activeLeases = Lease::where('leases.status', 'Active')
                ->whereIn('tenant_id', $expiredTenantIds)
                ->get(['tenant_id', 'bed_id'])
                ->keyBy('tenant_id');

            $expiredLeasesByTenant = Lease::where('leases.status', 'Expired')
                ->whereIn('bed_id', function ($q) use ($unitIds) {
                    $q->select('bed_id')->from('beds')->whereIn('unit_id', $unitIds);
                })
                ->whereIn('tenant_id', $expiredTenantIds)
                ->orderBy('end_date', 'desc')
                ->get(['tenant_id', 'bed_id'])
                ->unique('tenant_id')
                ->keyBy('tenant_id');

            foreach ($expiredLeasesByTenant as $tid => $expiredLease) {
                $activeLease = $activeLeases->get($tid);

                if ($activeLease && $activeLease->bed_id !== $expiredLease->bed_id) {
                    $transferredCount++;
                } elseif (!$activeLease) {
                    $movedOutCount++;
                }
            }
        }

        $this->counts = [
            'current'     => $currentCount,
            'moving_out'  => $movingOutCount,
            'transferred' => $transferredCount,
            'moved_out'   => $movedOutCount,
        ];
    }

    private function applySearch(array $tenants): array
    {
        if (empty($this->search)) {
            return $tenants;
        }

        $term = strtolower($this->search);

        return array_values(array_filter($tenants, function ($t) use ($term) {
            $name    = strtolower(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''));
            $unit    = strtolower('unit ' . ($t['unit'] ?? ''));
            $unitNum = strtolower($t['unit'] ?? '');
            $bed     = strtolower('bed ' . ($t['bed_number'] ?? ''));
            $bedNum  = strtolower($t['bed_number'] ?? '');

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
