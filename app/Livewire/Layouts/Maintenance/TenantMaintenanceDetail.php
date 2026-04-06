<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Livewire\Concerns\WithNotifications;

class TenantMaintenanceDetail extends Component
{
    use WithNotifications, WithFileUploads;
    public $ticket             = null;
    public $ticketIdDisplay    = '';
    public bool $feedbackSubmitted = false;

    // Edit mode
    public bool $editing           = false;
    public string $editCategory    = '';
    public string $editDescription = '';
    public array $existingPhotos   = [];   // current saved photo paths
    public $newPhotos              = [];   // new uploads (Livewire temp files)

    // Cost visibility (read-only)
    public array $costItems     = [];
    public float $costTotal     = 0;

    // Activity timeline
    public array $activities    = [];

    public function mount(?int $initialRequestId = null): void
    {
        if ($initialRequestId) {
            $this->loadRequest($initialRequestId);
        }
    }

    #[On('tenantMaintenanceSelected')]
    public function loadRequest($requestId)
    {
        if ($requestId === null) {
            $this->ticket            = null;
            $this->ticketIdDisplay   = '';
            $this->feedbackSubmitted = false;
            return;
        }

        $this->fetchTicket($requestId);
    }

    /**
     * When the manager updates the status, this component refreshes
     * so the tenant sees the new status and updated timeline immediately.
     */
    #[On('refresh-maintenance-list')]
    public function refreshTicket()
    {
        if ($this->ticket) {
            $this->fetchTicket($this->ticket->request_id);
        }
    }

    /**
     * Save the tenant's feedback for the resolved ticket.
     * Called from Alpine via $wire.saveFeedback(rating, tag, comment).
     *
     * Stores the result in a `maintenance_feedback` table.
     * Migration example:
     *
     *   Schema::create('maintenance_feedback', function (Blueprint $t) {
     *       $t->id();
     *       $t->unsignedBigInteger('request_id');
     *       $t->unsignedBigInteger('tenant_id');
     *       $t->tinyInteger('rating');              // 1–5
     *       $t->string('experience_tag')->nullable();
     *       $t->text('comment')->nullable();
     *       $t->timestamps();
     *   });
     */
    public function saveFeedback(int $rating, string $tag = '', string $comment = ''): void
    {
        if (!$this->ticket) {
            return;
        }

        // Verify this ticket belongs to the authenticated tenant
        $ownsTicket = DB::table('leases')
            ->where('lease_id', $this->ticket->lease_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if (!$ownsTicket) {
            abort(403, 'Unauthorized action.');
        }

        $rating = max(1, min(5, $rating));

        $existing = DB::table('maintenance_feedback')
            ->where('request_id', $this->ticket->request_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if ($existing) {
            DB::table('maintenance_feedback')
                ->where('request_id', $this->ticket->request_id)
                ->where('tenant_id', Auth::id())
                ->update([
                    'rating'         => $rating,
                    'experience_tag' => $tag ?: null,
                    'comment'        => $comment ?: null,
                    'updated_at'     => now(),
                ]);
        } else {
            DB::table('maintenance_feedback')->insert([
                'request_id'     => $this->ticket->request_id,
                'tenant_id'      => Auth::id(),
                'rating'         => $rating,
                'experience_tag' => $tag ?: null,
                'comment'        => $comment ?: null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $this->feedbackSubmitted = true;
        $this->notifySuccess('Feedback Submitted', 'Thank you for your feedback on this maintenance request.');
    }

    /**
     * Enter edit mode for a pending request.
     */
    public function startEditing(): void
    {
        if (!$this->ticket || $this->ticket->status !== 'Pending') return;
        $this->editCategory    = $this->ticket->category ?? 'Plumbing';
        $this->editDescription = $this->ticket->problem ?? '';
        $this->newPhotos       = [];

        // Parse existing photos
        $this->existingPhotos = [];
        if (!empty($this->ticket->image_path)) {
            $decoded = json_decode($this->ticket->image_path, true);
            $this->existingPhotos = is_array($decoded) ? $decoded : [$this->ticket->image_path];
        }

        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->newPhotos = [];
        $this->existingPhotos = [];
        $this->resetValidation();
    }

    public function removeExistingPhoto(int $index): void
    {
        array_splice($this->existingPhotos, $index, 1);
    }

    public function removeNewPhoto(int $index): void
    {
        array_splice($this->newPhotos, $index, 1);
    }

    /**
     * Save edits to a pending request.
     */
    public function saveEdit(): void
    {
        if (!$this->ticket || $this->ticket->status !== 'Pending') return;

        $ownsTicket = DB::table('leases')
            ->where('lease_id', $this->ticket->lease_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if (!$ownsTicket) {
            abort(403, 'Unauthorized action.');
        }

        $totalPhotos = count($this->existingPhotos) + count($this->newPhotos);

        $this->validate([
            'editCategory'    => 'required|in:Plumbing,Electrical,Structural,Appliance,Pest Control',
            'editDescription' => 'required|string|min:10|max:2000',
            'newPhotos'       => 'nullable|array|max:' . (3 - count($this->existingPhotos)),
            'newPhotos.*'     => 'image|max:5120',
        ]);

        // Build final photo array: existing + new uploads
        $allPhotos = $this->existingPhotos;
        foreach (array_slice($this->newPhotos, 0, 3 - count($allPhotos)) as $photo) {
            $allPhotos[] = $photo->store('maintenance_photos', 'public');
        }

        // Re-evaluate urgency based on new content
        $newUrgency = \App\Services\UrgencyEvaluator::evaluate($this->editCategory, $this->editDescription);

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'category'   => $this->editCategory,
                'problem'    => $this->editDescription,
                'urgency'    => $newUrgency,
                'image_path' => !empty($allPhotos) ? json_encode($allPhotos) : null,
                'updated_at' => now(),
            ]);

        $this->editing = false;
        $this->newPhotos = [];
        $this->existingPhotos = [];
        $this->fetchTicket($this->ticket->request_id);
        $this->dispatch('refresh-maintenance-list');
    }

    /**
     * Reopen a completed ticket — sets it back to Pending and notifies the manager.
     */
    public function reopenRequest(): void
    {
        if (!$this->ticket || $this->ticket->status !== 'Completed') {
            return;
        }

        // Verify ownership
        $ownsTicket = DB::table('leases')
            ->where('lease_id', $this->ticket->lease_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if (!$ownsTicket) {
            abort(403, 'Unauthorized action.');
        }

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'status'     => 'Pending',
                'updated_at' => now(),
            ]);

        // Notify the manager
        $unit = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $this->ticket->lease_id)
            ->select('units.manager_id')
            ->first();

        if ($unit && $unit->manager_id) {
            $ticketNum = $this->ticketIdDisplay;
            $user = Auth::user();
            Notification::create([
                'user_id' => $unit->manager_id,
                'type'    => 'maintenance_reopened',
                'title'   => 'Maintenance Request Reopened',
                'message' => "{$user->first_name} {$user->last_name} reopened maintenance request ({$ticketNum}). The issue may not be fully resolved.",
                'link'    => route('manager.maintenance'),
            ]);
        }

        $this->feedbackSubmitted = false;
        $this->fetchTicket($this->ticket->request_id);
        $this->notifySuccess('Request Reopened', 'Your maintenance request has been reopened and set back to Pending.');
        $this->dispatch('close-modal', 'confirm-reopen-request');
        $this->dispatch('refresh-maintenance-list');
    }

    /**
     * Cancel a pending maintenance request (soft delete).
     */
    public function cancelRequest(): void
    {
        if (!$this->ticket || $this->ticket->status !== 'Pending') {
            return;
        }

        // Verify ownership
        $ownsTicket = DB::table('leases')
            ->where('lease_id', $this->ticket->lease_id)
            ->where('tenant_id', Auth::id())
            ->exists();

        if (!$ownsTicket) {
            abort(403, 'Unauthorized action.');
        }

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update(['deleted_at' => now()]);

        $this->ticket = null;
        $this->ticketIdDisplay = '';
        $this->notifySuccess('Request Cancelled', 'Your maintenance request has been cancelled.');
        $this->dispatch('close-modal', 'confirm-cancel-request');
        $this->dispatch('refresh-maintenance-list');
    }

    private function fetchTicket($requestId): void
    {
        // Only load tickets that belong to this tenant's leases
        $tenantLeaseIds = DB::table('leases')
            ->where('tenant_id', Auth::id())
            ->pluck('lease_id');

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $requestId)
            ->whereIn('lease_id', $tenantLeaseIds)
            ->whereNull('deleted_at')
            ->first();

        if ($this->ticket) {
            $this->ticketIdDisplay = $this->ticket->ticket_number
                ?? 'TKT-' . str_pad($this->ticket->request_id, 4, '0', STR_PAD_LEFT);

            // Check if feedback was already submitted for this ticket
            $this->feedbackSubmitted = DB::table('maintenance_feedback')
                ->where('request_id', $this->ticket->request_id)
                ->where('tenant_id', Auth::id())
                ->exists();

            // Load costs for this ticket (read-only visibility)
            $this->loadCosts();
            $this->loadActivities();
        }
    }

    private function loadActivities(): void
    {
        if (!$this->ticket) {
            $this->activities = [];
            return;
        }

        // Load public-facing activities (exclude internal notes)
        $this->activities = DB::table('maintenance_activities')
            ->where('request_id', $this->ticket->request_id)
            ->where('action', '!=', 'note_added')
            ->orderBy('created_at', 'desc')
            ->select('action', 'details', 'created_at')
            ->get()
            ->map(fn($a) => (array) $a)
            ->toArray();
    }

    private function loadCosts(): void
    {
        if (!$this->ticket) {
            $this->costItems = [];
            $this->costTotal = 0;
            return;
        }

        $this->costItems = DB::table('maintenance_logs')
            ->where('request_id', $this->ticket->request_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        $this->costTotal = collect($this->costItems)->sum('cost');
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.tenant-maintenance-detail');
    }
}
