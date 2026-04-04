<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\MaintenanceActivity;
use App\Models\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerMaintenanceList extends Component
{
    // Tabs are all/pending/ongoing/completed
    public $activeTab = 'all';
    public $activeRequestId = null;

    // ADDED: Sort order property initialized to 'newest'
    public $sortOrder = 'newest';

    // Building filter
    public $selectedBuilding = null;

    // Search
    public $search = '';

    // Bulk selection
    public $selectedIds = [];
    public $selectAll = false;

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

    public function updatedSelectAll($value)
    {
        // This is handled by Alpine on the frontend — selectedIds syncs via wire:model
    }

    public function bulkUpdateStatus($newStatus)
    {
        if (empty($this->selectedIds) || !in_array($newStatus, ['Ongoing', 'Completed'])) return;

        $managerId = Auth::id();

        // Only update requests that belong to this manager's units
        $validIds = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->whereIn('maintenance_requests.request_id', $this->selectedIds)
            ->where('units.manager_id', $managerId)
            ->pluck('maintenance_requests.request_id')
            ->toArray();

        if (empty($validIds)) return;

        DB::table('maintenance_requests')
            ->whereIn('request_id', $validIds)
            ->update(['status' => $newStatus, 'updated_at' => now()]);

        // Log activity & notify tenants for each
        foreach ($validIds as $rid) {
            MaintenanceActivity::create([
                'request_id' => $rid,
                'user_id'    => $managerId,
                'action'     => 'status_changed',
                'details'    => "Bulk status change to {$newStatus}.",
            ]);

            $tenantId = DB::table('maintenance_requests')
                ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
                ->where('maintenance_requests.request_id', $rid)
                ->value('leases.tenant_id');

            if ($tenantId) {
                $ticket = DB::table('maintenance_requests')->where('request_id', $rid)->first();
                Notification::create([
                    'user_id' => $tenantId,
                    'type'    => 'maintenance_update',
                    'title'   => $newStatus === 'Completed' ? 'Maintenance Completed' : 'Maintenance In Progress',
                    'message' => "Your maintenance request ({$ticket->ticket_number}) has been updated to {$newStatus}.",
                    'link'    => route('tenant.maintenance'),
                ]);
            }
        }

        $this->selectedIds = [];
        $this->selectAll = false;
        $this->dispatch('managerMaintenanceSelected', requestId: null);
    }

    public function exportCsv()
    {
        $managerId = Auth::id();

        $rows = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_requests.deleted_at')
            ->when($this->selectedBuilding, fn($q) => $q->where('properties.building_name', $this->selectedBuilding))
            ->when($this->activeTab !== 'all', fn($q) => $q->where('maintenance_requests.status', ucfirst($this->activeTab)))
            ->select(
                'maintenance_requests.ticket_number',
                'maintenance_requests.status',
                'maintenance_requests.category',
                'maintenance_requests.urgency',
                'maintenance_requests.problem',
                'maintenance_requests.assigned_to',
                'maintenance_requests.expected_completion_date',
                'maintenance_requests.created_at',
                'units.unit_number',
                'properties.building_name',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            )
            ->orderBy('maintenance_requests.created_at', 'desc')
            ->get();

        // Also get total cost per request
        $costs = DB::table('maintenance_logs')
            ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
            ->whereIn('maintenance_requests.ticket_number', $rows->pluck('ticket_number'))
            ->whereNull('maintenance_logs.deleted_at')
            ->groupBy('maintenance_requests.ticket_number')
            ->select('maintenance_requests.ticket_number', DB::raw('SUM(maintenance_logs.cost) as total_cost'))
            ->pluck('total_cost', 'maintenance_requests.ticket_number');

        return response()->streamDownload(function () use ($rows, $costs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ticket', 'Status', 'Category', 'Priority', 'Tenant', 'Unit', 'Building', 'Assigned To', 'Expected Completion', 'Total Cost', 'Description', 'Submitted']);

            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->ticket_number,
                    $r->status,
                    $r->category,
                    $r->urgency,
                    $r->tenant_name,
                    'Unit ' . $r->unit_number,
                    $r->building_name,
                    $r->assigned_to ?? '',
                    $r->expected_completion_date ?? '',
                    number_format($costs[$r->ticket_number] ?? 0, 2),
                    $r->problem,
                    $r->created_at,
                ]);
            }

            fclose($handle);
        }, 'maintenance-report-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $managerId = Auth::id();

        // Base query — joins through lease → bed → unit → property to find tickets for this manager's units
        $baseQuery = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('units.manager_id', $managerId)
            ->whereNull('maintenance_requests.deleted_at')
            ->when($this->selectedBuilding, function ($query) {
                $query->where('properties.building_name', $this->selectedBuilding);
            })
            ->select(
                'maintenance_requests.request_id',
                'maintenance_requests.status',
                'maintenance_requests.category',
                'maintenance_requests.ticket_number',
                'maintenance_requests.created_at',
                'units.unit_number',
                'properties.building_name',
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

        // Auto-select first request if none is selected
        if ($this->activeRequestId === null && $requests->isNotEmpty()) {
            $this->selectRequest($requests->first()->request_id);
        }

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

        $buildingOptions = [];
        try {
            $buildingOptions = Property::distinct()->pluck('building_name', 'building_name')->toArray();
        } catch (\Exception $e) { $buildingOptions = []; }

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
            'buildingOptions' => $buildingOptions,
        ]);
    }
}
