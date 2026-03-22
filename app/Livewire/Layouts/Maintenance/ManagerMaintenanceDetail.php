<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class ManagerMaintenanceDetail extends Component
{
    public $ticket          = null;
    public $ticketIdDisplay = '';
    public $successMessage  = '';
    public $feedback        = null; // tenant's feedback for this ticket

    // Cost tracking
    public $showCostForm    = false;
    public $costAmount      = '';
    public $costDescription = '';
    public $costItems       = [];
    public $requestTotal    = 0;
    public $unitTotal       = 0;
    public $unitId          = null;

    #[On('managerMaintenanceSelected')]
    public function loadRequest($requestId)
    {
        if ($requestId === null) {
            $this->ticket   = null;
            $this->feedback = null;
            $this->resetCostForm();
            return;
        }

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $requestId)
            ->first();

        if ($this->ticket) {
            $this->ticketIdDisplay = $this->ticket->ticket_number
                ?? 'TKT-' . str_pad($this->ticket->request_id, 4, '0', STR_PAD_LEFT);

            // Load tenant feedback for this ticket (if submitted)
            $this->feedback = DB::table('maintenance_feedback')
                ->where('request_id', $this->ticket->request_id)
                ->first();

            // Load cost data
            $this->loadCostData();
        }

        $this->successMessage = '';
    }

    /**
     * Load cost items for this ticket and calculate totals.
     */
    public function loadCostData()
    {
        if (!$this->ticket) return;

        // Load cost items for this request
        $this->costItems = DB::table('maintenance_logs')
            ->where('request_id', $this->ticket->request_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // Request total
        $this->requestTotal = collect($this->costItems)->sum('cost');

        // Get unit_id for this ticket via lease -> bed -> unit
        $unitInfo = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $this->ticket->lease_id)
            ->select('units.unit_id')
            ->first();

        $this->unitId = $unitInfo?->unit_id;

        // Calculate total maintenance cost for this unit (all completed requests)
        if ($this->unitId) {
            $this->unitTotal = DB::table('maintenance_logs')
                ->join('maintenance_requests', 'maintenance_logs.request_id', '=', 'maintenance_requests.request_id')
                ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->where('beds.unit_id', $this->unitId)
                ->whereNull('maintenance_logs.deleted_at')
                ->sum('maintenance_logs.cost');
        }
    }

    /**
     * Toggle the cost entry form.
     */
    public function toggleCostForm()
    {
        $this->showCostForm = !$this->showCostForm;
        if (!$this->showCostForm) {
            $this->costAmount = '';
            $this->costDescription = '';
        }
    }

    /**
     * Save a cost item for this maintenance request.
     */
    public function saveCost()
    {
        $this->validate([
            'costAmount'      => 'required|numeric|min:1|max:999999.99',
            'costDescription' => 'required|string|min:3|max:255',
        ], [
            'costAmount.required'      => 'Please enter a cost amount.',
            'costAmount.numeric'       => 'Amount must be a valid number.',
            'costAmount.min'           => 'Cost must be at least PHP 1.00.',
            'costAmount.max'           => 'Cost cannot exceed PHP 999,999.99.',
            'costDescription.required' => 'Please enter a description.',
            'costDescription.min'      => 'Description must be at least 3 characters.',
            'costDescription.max'      => 'Description cannot exceed 255 characters.',
        ]);

        DB::table('maintenance_logs')->insert([
            'request_id'      => $this->ticket->request_id,
            'completion_date' => now()->toDateString(),
            'cost'            => round((float) $this->costAmount, 2),
            'description'     => $this->costDescription,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->costAmount = '';
        $this->costDescription = '';
        $this->showCostForm = false;
        $this->successMessage = 'Cost item added successfully.';

        $this->loadCostData();
        $this->dispatch('refreshDashboard');
    }

    /**
     * Remove a cost item.
     */
    public function removeCostItem($logId)
    {
        DB::table('maintenance_logs')
            ->where('log_id', $logId)
            ->update(['deleted_at' => now()]);

        $this->successMessage = 'Cost item removed.';
        $this->loadCostData();
        $this->dispatch('refreshDashboard');
    }

    /**
     * Reset cost form state.
     */
    private function resetCostForm()
    {
        $this->showCostForm = false;
        $this->costAmount = '';
        $this->costDescription = '';
        $this->costItems = [];
        $this->requestTotal = 0;
        $this->unitTotal = 0;
        $this->unitId = null;
    }

    /**
     * Manager marks the request as Ongoing (In Progress).
     */
    public function markAsOngoing()
    {
        if (!$this->ticket) return;

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'status'     => 'Ongoing',
                'updated_at' => now(),
            ]);

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->successMessage = 'Status updated to Ongoing.';

        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    /**
     * Manager marks the request as Completed.
     */
    public function markAsCompleted()
    {
        if (!$this->ticket) return;

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'status'     => 'Completed',
                'updated_at' => now(),
            ]);

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->feedback = DB::table('maintenance_feedback')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->successMessage = 'Request marked as Completed.';

        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.manager-maintenance-detail');
    }
}
