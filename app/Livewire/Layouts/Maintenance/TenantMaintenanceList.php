<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantMaintenanceList extends Component
{
    public $activeRequestId = null;
    public $activeTab = 'all';
    public $sortOrder = 'newest';
    public $search = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->activeRequestId = null;
        $this->dispatch('tenantMaintenanceSelected', requestId: null);
    }

    public function selectRequest($id)
    {
        $this->activeRequestId = $id;
        $this->dispatch('tenantMaintenanceSelected', requestId: $id);
    }

    #[On('refresh-maintenance-list')]
    public function refreshList()
    {
        // This empty method triggers a re-render
    }

    public function updatedSearch()
    {
        $this->activeRequestId = null;
    }

    public function render()
    {
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $baseQuery = DB::table('maintenance_requests')
            ->whereIn('lease_id', $tenantLeaseIds)
            ->whereNull('deleted_at');

        // Apply search filter
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', $search)
                  ->orWhere('category', 'like', $search)
                  ->orWhere('status', 'like', $search);
            });
        }

        $statusCountsRaw = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $counts = [
            'all'         => (clone $baseQuery)->count(),
            'pending'     => $statusCountsRaw['Pending'] ?? 0,
            'ongoing'     => $statusCountsRaw['Ongoing'] ?? 0,
            'completed'   => $statusCountsRaw['Completed'] ?? 0,
        ];

        $query = (clone $baseQuery)->select(
            'request_id',
            'status',
            'urgency',
            'category',
            'problem',
            'created_at',
            'ticket_number'
        );

        switch ($this->activeTab) {
            case 'pending':
                $query->where('status', 'Pending');
                break;
            case 'ongoing':
                $query->where('status', 'Ongoing');
                break;
            case 'completed':
                $query->where('status', 'Completed');
                break;
        }

        $direction = $this->sortOrder === 'newest' ? 'desc' : 'asc';
        $requests = $query->orderBy('created_at', $direction)->get();

        // Auto-select first request if none is selected
        if ($this->activeRequestId === null && $requests->isNotEmpty()) {
            $this->selectRequest($requests->first()->request_id);
        }

        // Build suggestions from all requests (unfiltered)
        $allRequests = DB::table('maintenance_requests')
            ->whereIn('lease_id', $tenantLeaseIds)
            ->select('ticket_number', 'category')
            ->get();

        $suggestions = collect()
            ->merge($allRequests->pluck('ticket_number')->filter())
            ->merge($allRequests->pluck('category')->filter())
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.layouts.maintenance.tenant-maintenance-list', [
            'requests' => $requests,
            'counts' => $counts,
            'activeTab' => $this->activeTab,
            'activeRequestId' => $this->activeRequestId,
            'sortOrder' => $this->sortOrder,
            'suggestions' => $suggestions,
        ]);
    }
}
