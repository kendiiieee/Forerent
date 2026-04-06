<?php

namespace App\Livewire\Layouts\Violations;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Livewire\Concerns\WithNotifications;

class TenantViolationDetail extends Component
{
    use WithNotifications;

    public $violation = null;
    public $violationIdDisplay = '';
    public $offenseHistory = [];

    public function mount(?int $initialViolationId = null): void
    {
        if ($initialViolationId) {
            $this->loadViolation($initialViolationId);
        }
    }

    #[On('tenantViolationSelected')]
    public function loadViolation($violationId)
    {
        if ($violationId === null) {
            $this->violation = null;
            $this->violationIdDisplay = '';
            return;
        }

        $this->fetchViolation($violationId);
    }

    #[On('refresh-violation-list')]
    public function refreshViolation()
    {
        if ($this->violation) {
            $this->fetchViolation($this->violation->violation_id);
        }
    }

    /**
     * Tenant acknowledges receipt of the violation notice.
     */
    public function acknowledgeViolation(): void
    {
        if (!$this->violation || $this->violation->status !== 'Issued') {
            return;
        }

        // Verify ownership
        $ownsViolation = DB::table('leases')
            ->where('lease_id', $this->violation->lease_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if (!$ownsViolation) {
            abort(403, 'Unauthorized action.');
        }

        DB::table('violations')
            ->where('violation_id', $this->violation->violation_id)
            ->update([
                'status' => 'Acknowledged',
                'tenant_acknowledged_at' => now(),
                'updated_at' => now(),
            ]);

        // Notify the manager
        $unit = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $this->violation->lease_id)
            ->select('units.manager_id')
            ->first();

        if ($unit && $unit->manager_id) {
            $user = Auth::user();
            Notification::create([
                'user_id' => $unit->manager_id,
                'type' => 'violation_acknowledged',
                'title' => 'Violation Acknowledged',
                'message' => "{$user->first_name} {$user->last_name} has acknowledged violation {$this->violationIdDisplay}.",
                'link' => route('manager.tenant'),
            ]);
        }

        $this->fetchViolation($this->violation->violation_id);
        $this->notifySuccess('Violation Acknowledged', 'You have acknowledged this violation notice.');
        $this->dispatch('refresh-violation-list');
    }

    private function fetchViolation($violationId): void
    {
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $this->violation = DB::table('violations')
            ->where('violation_id', $violationId)
            ->whereIn('lease_id', $tenantLeaseIds)
            ->whereNull('deleted_at')
            ->first();

        if ($this->violation) {
            $this->violationIdDisplay = $this->violation->violation_number
                ?? 'VIO-' . str_pad($this->violation->violation_id, 4, '0', STR_PAD_LEFT);

            $this->loadOffenseHistory();
        }
    }

    private function loadOffenseHistory(): void
    {
        if (!$this->violation) {
            $this->offenseHistory = [];
            return;
        }

        $this->offenseHistory = DB::table('violations')
            ->where('lease_id', $this->violation->lease_id)
            ->whereNull('deleted_at')
            ->orderBy('offense_number', 'asc')
            ->select('violation_id', 'violation_number', 'offense_number', 'category', 'severity', 'penalty_type', 'status', 'violation_date', 'fine_amount')
            ->get()
            ->map(fn($v) => (array) $v)
            ->toArray();
    }

    public function render()
    {
        return view('livewire.layouts.violations.tenant-violation-detail');
    }
}
