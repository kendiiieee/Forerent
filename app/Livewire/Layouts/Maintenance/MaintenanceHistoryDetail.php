<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceHistoryDetail extends Component
{
    public $currentHistoryId = null;
    public $currentHistoryItem = null;

    #[On('maintenanceHistorySelected')]
    public function loadHistoryItem(?int $historyId): void
    {
        if (!$historyId) {
            $this->resetHistoryData();
            return;
        }

        $user = Auth::user();

        $query = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('maintenance_requests.request_id', $historyId)
            ->whereNull('maintenance_requests.deleted_at');

        // Role-based access
        if ($user->role === 'tenant') {
            $query->where('leases.tenant_id', $user->user_id);
        } elseif ($user->role === 'manager') {
            $query->where('units.manager_id', $user->user_id);
        }

        $record = $query->select(
            'maintenance_requests.request_id',
            'maintenance_requests.lease_id',
            'maintenance_requests.status',
            'maintenance_requests.logged_by',
            'maintenance_requests.ticket_number',
            'maintenance_requests.log_date',
            'maintenance_requests.problem',
            'maintenance_requests.urgency',
            'maintenance_requests.category',
            'maintenance_requests.assigned_to',
            'maintenance_requests.expected_completion_date',
            'maintenance_requests.created_at',
            'maintenance_requests.updated_at',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name"),
            'properties.building_name',
            DB::raw("'Unit ' || units.unit_number as unit_number")
        )->first();

        if (!$record) {
            $this->resetHistoryData();
            return;
        }

        // Get total cost from maintenance_logs
        $costData = DB::table('maintenance_logs')
            ->where('request_id', $historyId)
            ->whereNull('deleted_at')
            ->selectRaw('SUM(cost) as total_cost, MAX(completion_date) as completion_date')
            ->first();

        $this->currentHistoryId = $historyId;
        $this->currentHistoryItem = array_merge((array) $record, [
            'cost'            => $costData->total_cost ?? 0,
            'completion_date' => $costData->completion_date ?? null,
        ]);
    }

    private function resetHistoryData(): void
    {
        $this->currentHistoryId = null;
        $this->currentHistoryItem = null;
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.maintenance-history-detail');
    }
}
