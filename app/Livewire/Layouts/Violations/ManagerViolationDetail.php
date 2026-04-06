<?php

namespace App\Livewire\Layouts\Violations;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Violation;
use App\Livewire\Concerns\WithNotifications;

class ManagerViolationDetail extends Component
{
    use WithNotifications;

    public $violation = null;
    public $violationIdDisplay = '';
    public $successMessage = '';

    // Resolution
    public $resolutionNotes = '';

    // Offense history for this tenant's lease
    public $offenseHistory = [];

    public function mount(?int $initialViolationId = null): void
    {
        if ($initialViolationId) {
            $this->loadViolation($initialViolationId);
        }
    }

    #[On('managerViolationSelected')]
    public function loadViolation($violationId)
    {
        if ($violationId === null) {
            $this->violation = null;
            return;
        }

        $this->violation = DB::table('violations')
            ->join('leases', 'violations.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('violations.violation_id', $violationId)
            ->where('units.manager_id', Auth::id())
            ->whereNull('violations.deleted_at')
            ->select('violations.*')
            ->first();

        if ($this->violation) {
            $this->violationIdDisplay = $this->violation->violation_number
                ?? 'VIO-' . str_pad($this->violation->violation_id, 4, '0', STR_PAD_LEFT);

            $this->resolutionNotes = $this->violation->resolution_notes ?? '';
            $this->loadOffenseHistory();
        }

        $this->successMessage = '';
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

    private function authorizeManagerForViolation(): bool
    {
        if (!$this->violation) return false;

        return DB::table('violations')
            ->join('leases', 'violations.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('violations.violation_id', $this->violation->violation_id)
            ->where('units.manager_id', Auth::id())
            ->exists();
    }

    public function resolveViolation(): void
    {
        if (!$this->authorizeManagerForViolation()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'resolutionNotes' => 'required|string|min:3|max:1000',
        ], [
            'resolutionNotes.required' => 'Please enter resolution notes.',
            'resolutionNotes.min' => 'Resolution notes must be at least 3 characters.',
        ]);

        DB::table('violations')
            ->where('violation_id', $this->violation->violation_id)
            ->update([
                'status' => 'Resolved',
                'resolution_notes' => $this->resolutionNotes,
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);

        $this->notifyTenant(
            'Violation Resolved',
            "Your violation ({$this->violationIdDisplay}) has been marked as resolved."
        );

        $this->notifySuccess('Violation Resolved', 'The violation has been marked as resolved.');
        $this->violation = DB::table('violations')->where('violation_id', $this->violation->violation_id)->first();
        $this->loadOffenseHistory();

        $this->dispatch('close-modal', 'confirm-resolve-violation');
        $this->dispatch('refresh-violation-list');
    }

    public function archiveViolation(): void
    {
        if (!$this->authorizeManagerForViolation()) {
            abort(403, 'Unauthorized action.');
        }

        DB::table('violations')
            ->where('violation_id', $this->violation->violation_id)
            ->update(['deleted_at' => now()]);

        $this->violation = null;
        $this->offenseHistory = [];

        $this->notifySuccess('Violation Archived', 'The violation record has been archived.');
        $this->dispatch('close-modal', 'confirm-archive-violation');
        $this->dispatch('refresh-violation-list');
    }

    private function notifyTenant(string $title, string $message): void
    {
        if (!$this->violation) return;

        $tenantId = DB::table('leases')
            ->where('lease_id', $this->violation->lease_id)
            ->value('tenant_id');

        if ($tenantId) {
            Notification::create([
                'user_id' => $tenantId,
                'type' => 'violation_update',
                'title' => $title,
                'message' => $message,
                'link' => route('tenant.dashboard'),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.layouts.violations.manager-violation-detail');
    }
}
