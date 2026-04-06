<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceHistoryList extends Component
{
    public $filter = 'all';
    public $activeHistoryId = null;

    #[On('refreshDashboard')]
    public function refreshDashboard() {}

    public function render()
    {
        $user = Auth::user();

        // Base Query
        $query = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->whereNull('maintenance_requests.deleted_at')
            ->select(
                'maintenance_requests.request_id',
                'maintenance_requests.status',
                'maintenance_requests.urgency',
                'maintenance_requests.created_at',
                'maintenance_requests.problem',
                'units.unit_number',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            );

        // --- ROLE BASED FILTERING ---

        if ($user->role === 'tenant') {
            // Tenant sees ONLY their own requests
            $query->where('leases.tenant_id', $user->user_id);
        } elseif ($user->role === 'manager') {
            // Manager sees requests for units they manage
            $query->where('units.manager_id', $user->user_id);
        }
        // Landlord sees everything (no where clause needed)

        // ----------------------------

        if ($this->filter !== 'all') {
            $query->where('maintenance_requests.status', $this->filter);
        }

        $historyItems = $query->orderBy('created_at', 'desc')->get();

        // Auto-select first item if none is selected
        if ($this->activeHistoryId === null && $historyItems->isNotEmpty()) {
            $this->selectHistory($historyItems->first()->request_id);
        }

        return view('livewire.layouts.maintenance.maintenance-history-list', [
            'historyItems' => $historyItems,
        ]);
    }

    public function selectHistory($id)
    {
        $this->activeHistoryId = $id;
        $this->dispatch('maintenanceHistorySelected', historyId: $id);
    }
}
