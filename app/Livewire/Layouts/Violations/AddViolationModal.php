<?php

namespace App\Livewire\Layouts\Violations;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Lease;
use App\Services\ViolationEscalationService;
use App\Livewire\Concerns\WithNotifications;

class AddViolationModal extends Component
{
    use WithFileUploads, WithNotifications;

    public $isOpen = false;

    // Form fields
    public $leaseId = null;
    public $category = 'Noise Violation';
    public $severity = 'minor';
    public $description = '';
    public $violationDate = '';
    public $evidencePhotos = [];

    // Tenant selector
    public $tenantLeases = [];

    // Preview of what penalty will be applied
    public $penaltyPreview = null;

    #[On('open-add-violation-modal')]
    public function open($leaseId = null)
    {
        $this->resetForm();
        $this->loadTenantLeases();
        $this->violationDate = now()->format('Y-m-d');

        // Pre-select lease if provided (e.g. from tenant detail)
        if ($leaseId) {
            $this->leaseId = $leaseId;
            $this->updatePenaltyPreview();
        }

        $this->isOpen = true;
    }

    public function loadTenantLeases()
    {
        $managerId = Auth::id();

        $this->tenantLeases = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->join('properties', 'units.property_id', '=', 'properties.property_id')
            ->join('users', 'leases.tenant_id', '=', 'users.user_id')
            ->where('units.manager_id', $managerId)
            ->where('leases.status', 'Active')
            ->whereNull('leases.deleted_at')
            ->select(
                'leases.lease_id',
                'units.unit_number',
                'properties.building_name',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as tenant_name")
            )
            ->orderBy('users.last_name')
            ->get()
            ->map(fn($l) => (array) $l)
            ->toArray();
    }

    public function updatedLeaseId()
    {
        $this->updatePenaltyPreview();
    }

    public function updatedSeverity()
    {
        $this->updatePenaltyPreview();
    }

    private function updatePenaltyPreview(): void
    {
        if (!$this->leaseId) {
            $this->penaltyPreview = null;
            return;
        }

        $lease = Lease::find($this->leaseId);
        if (!$lease) {
            $this->penaltyPreview = null;
            return;
        }

        $this->penaltyPreview = ViolationEscalationService::determinePenalty($lease, $this->severity);
    }

    public function removePhoto($index)
    {
        array_splice($this->evidencePhotos, $index, 1);
    }

    public function save()
    {
        $this->validate([
            'leaseId' => 'required|exists:leases,lease_id',
            'category' => 'required|string|max:255',
            'severity' => 'required|in:minor,major,serious',
            'description' => 'required|string|min:10|max:2000',
            'violationDate' => 'required|date|before_or_equal:today',
            'evidencePhotos' => 'nullable|array|max:3',
            'evidencePhotos.*' => 'image|max:5120',
        ], [
            'leaseId.required' => 'Please select a tenant.',
            'description.required' => 'Please describe the violation.',
            'description.min' => 'Description must be at least 10 characters.',
            'violationDate.required' => 'Please set the violation date.',
            'violationDate.before_or_equal' => 'Violation date cannot be in the future.',
        ]);

        // Verify manager has authority over this lease
        $authorized = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $this->leaseId)
            ->where('units.manager_id', Auth::id())
            ->exists();

        if (!$authorized) {
            abort(403, 'Unauthorized action.');
        }

        $lease = Lease::findOrFail($this->leaseId);

        // Determine penalty based on offense history
        $penalty = ViolationEscalationService::determinePenalty($lease, $this->severity);

        // Store evidence photos
        $evidencePaths = [];
        if (!empty($this->evidencePhotos)) {
            foreach (array_slice($this->evidencePhotos, 0, 3) as $photo) {
                $evidencePaths[] = $photo->store('violation_evidence', 'public');
            }
        }

        // Generate violation number inside transaction
        $violation = null;
        DB::transaction(function () use ($lease, $penalty, $evidencePaths, &$violation) {
            DB::statement('SELECT pg_advisory_xact_lock(2)');
            $nextId = (DB::table('violations')->max('violation_id') ?? 0) + 1;
            $violationNumber = 'VIO-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $violationId = DB::table('violations')->insertGetId([
                'lease_id' => $this->leaseId,
                'reported_by' => Auth::id(),
                'violation_number' => $violationNumber,
                'offense_number' => $penalty['offense_number'],
                'category' => $this->category,
                'description' => $this->description,
                'evidence_path' => !empty($evidencePaths) ? json_encode($evidencePaths) : null,
                'severity' => $this->severity,
                'penalty_type' => $penalty['penalty_type'],
                'fine_amount' => $penalty['fine_amount'],
                'status' => 'Issued',
                'violation_date' => $this->violationDate,
                'issued_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $violation = \App\Models\Violation::find($violationId);
        });

        // Apply penalty (create billing item for fines)
        if ($violation) {
            ViolationEscalationService::applyPenalty($violation);

            // Notify tenant
            $penaltyLabel = match($penalty['penalty_type']) {
                'written_warning' => 'Written Warning issued',
                'fine' => 'Fine of PHP ' . number_format($penalty['fine_amount'], 2) . ' charged to your billing',
                'lease_termination' => 'This is grounds for lease termination',
                default => '',
            };

            $tenantId = $lease->tenant_id;
            Notification::create([
                'user_id' => $tenantId,
                'type' => 'violation_issued',
                'title' => 'Violation Notice — ' . $violation->violation_number,
                'message' => "A {$this->severity} violation ({$this->category}) has been recorded. {$penaltyLabel}.",
                'link' => route('tenant.dashboard'),
            ]);

            // If lease termination, also alert the manager prominently
            if ($penalty['penalty_type'] === 'lease_termination') {
                $this->notifyWarning(
                    'Lease Termination Flagged',
                    "This is the {$penalty['offense_number']}th offense. Consider initiating the move-out process for this tenant."
                );
            }
        }

        $this->notifySuccess('Violation Recorded', "Violation {$violation->violation_number} has been issued ({$penalty['penalty_type']}).");
        $this->dispatch('refresh-violation-list');
        $this->close();
    }

    public function close()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['leaseId', 'description', 'evidencePhotos', 'penaltyPreview']);
        $this->category = 'Noise Violation';
        $this->severity = 'minor';
        $this->violationDate = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.layouts.violations.add-violation-modal');
    }
}
