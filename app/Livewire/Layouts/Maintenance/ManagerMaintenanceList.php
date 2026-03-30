<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerMaintenanceList extends Component
{
    // Tabs are all/pending/ongoing/completed
    public $activeTab = 'all';
    public $activeRequestId = null;

    // ADDED: Sort order property initialized to 'newest'
    public $sortOrder = 'newest';

    // Search
    public $search = '';

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function updatedSearch()
    {
        $this->activeRequestId = null;
    }

    #[On('refresh-maintenance-list')]
    public function refreshList()
    {
        // Event-driven refresh after create/status updates.
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeRequestId = null;
    }

    public function selectRequest($id)
    {
        $this->activeRequestId = $id;
        $this->dispatch('managerMaintenanceSelected', requestId: $id);
    }

    public function render()
    {
        $managerId = Auth::id();

        // Base query — joins through lease → bed → unit to find tickets for this manager's units
        $baseQuery = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('units.manager_id', $managerId)
            ->select(
                'maintenance_requests.request_id',
                'maintenance_requests.status',
                'maintenance_requests.category',
                'maintenance_requests.ticket_number',
                'maintenance_requests.created_at',
                'units.unit_number',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            );

        // Apply search filter to base query if searching
        if (!empty($this->search)) {
            $term = $this->search;
            $unitTerm = preg_replace('/^Unit\s+/i', '', $term);
            $search = '%' . $term . '%';
            $unitSearch = '%' . $unitTerm . '%';
            $baseQuery->where(function ($q) use ($search, $unitSearch) {
                $q->where('maintenance_requests.ticket_number', 'like', $search)
                  ->orWhere('maintenance_requests.category', 'like', $search)
                  ->orWhere('units.unit_number', 'like', $unitSearch)
                  ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', $search);
            });
        }

        // Tab counts (reflect search filter)
        $allCount       = (clone $baseQuery)->count();
        $pendingCount   = (clone $baseQuery)->where('maintenance_requests.status', 'Pending')->count();
        $ongoingCount   = (clone $baseQuery)->where('maintenance_requests.status', 'Ongoing')->count();
        $completedCount = (clone $baseQuery)->where('maintenance_requests.status', 'Completed')->count();

        // Apply tab filter
        $listQuery = clone $baseQuery;
        switch ($this->activeTab) {
            case 'pending':
                $listQuery->where('maintenance_requests.status', 'Pending');
                break;
            case 'ongoing':
                $listQuery->where('maintenance_requests.status', 'Ongoing');
                break;
            case 'completed':
                $listQuery->where('maintenance_requests.status', 'Completed');
                break;
                // 'all' — no extra filter
        }

        // ADDED: Apply sorting direction based on the dropdown selection
        $direction = $this->sortOrder === 'newest' ? 'desc' : 'asc';
        $requests = $listQuery->orderBy('maintenance_requests.created_at', $direction)->get();

        // Build autocomplete suggestions from unfiltered results
        $allRequests = (clone $baseQuery)->orderBy('maintenance_requests.created_at', 'desc')->limit(200)->get();
        $suggestions = collect()
            ->merge($allRequests->pluck('ticket_number')->filter())
            ->merge($allRequests->pluck('tenant_name')->filter())
            ->merge($allRequests->map(fn($r) => 'Unit ' . $r->unit_number)->filter())
            ->merge($allRequests->pluck('category')->filter())
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.maintenance.manager-maintenance-list', [
            'requests' => $requests,
            'counts' => [
                'all'       => $allCount,
                'pending'   => $pendingCount,
                'ongoing'   => $ongoingCount,
                'completed' => $completedCount,
            ],
            'sortOrder' => $this->sortOrder,
            'suggestions' => $suggestions,
        ]);
    }
}
