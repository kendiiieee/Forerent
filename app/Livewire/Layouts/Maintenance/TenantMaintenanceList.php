<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantMaintenanceList extends Component
{
    public $activeRequestId = null;
    public $activeTab = 'all';
    // sort order for requests list (newest or oldest)
    public $sortOrder = 'newest';

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

    public function refreshList()
    {
        // This empty method triggers a re-render
    }

    public function render()
    {
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $baseCountQuery = DB::table('maintenance_requests')
            ->whereIn('lease_id', $tenantLeaseIds);

        $statusCountsRaw = (clone $baseCountQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $counts = [
            'all'         => (clone $baseCountQuery)->count(),
            'pending'     => $statusCountsRaw['Pending'] ?? 0,
            'ongoing'     => ($statusCountsRaw['In Progress'] ?? 0) + ($statusCountsRaw['Ongoing'] ?? 0),
            'completed'   => ($statusCountsRaw['Completed'] ?? 0) + ($statusCountsRaw['Resolved'] ?? 0),
        ];

        $query = DB::table('maintenance_requests')
            ->whereIn('lease_id', $tenantLeaseIds)
            ->select(
                'request_id',
                'status',
                'urgency',
                'category',      // <--- ADDED this line earlier
                'problem',
                'created_at',
                'ticket_number'
            );

        switch ($this->activeTab) {
            case 'pending':
                $query->where('status', 'Pending');
                break;
            case 'ongoing':
                $query->whereIn('status', ['In Progress', 'Ongoing']);
                break;
            case 'completed':
                $query->whereIn('status', ['Completed', 'Resolved']);
                break;
        }

        // apply sorting direction based on user selection
        $direction = $this->sortOrder === 'newest' ? 'desc' : 'asc';
        $requests = $query->orderBy('created_at', $direction)->get();

        return view('livewire.layouts.maintenance.tenant-maintenance-list', [
            'requests' => $requests,
            'counts' => $counts,
            'activeTab' => $this->activeTab,
            'activeRequestId' => $this->activeRequestId,
            'sortOrder' => $this->sortOrder,
        ]);
    }
}
