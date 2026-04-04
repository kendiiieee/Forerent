<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantMaintenanceDetail extends Component
{
    public $ticket             = null;
    public $ticketIdDisplay    = '';
    public bool $feedbackSubmitted = false;

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

        $rating = max(1, min(5, $rating));

        DB::table('maintenance_feedback')->updateOrInsert(
            [
                'request_id' => $this->ticket->request_id,
                'tenant_id'  => Auth::id(),
            ],
            [
                'rating'         => $rating,
                'experience_tag' => $tag ?: null,
                'comment'        => $comment ?: null,
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );

        $this->feedbackSubmitted = true;
    }

    private function fetchTicket($requestId): void
    {
        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $requestId)
            ->first();

        if ($this->ticket) {
            $this->ticketIdDisplay = $this->ticket->ticket_number
                ?? 'TKT-' . str_pad($this->ticket->request_id, 4, '0', STR_PAD_LEFT);

            // Check if feedback was already submitted for this ticket
            $this->feedbackSubmitted = DB::table('maintenance_feedback')
                ->where('request_id', $this->ticket->request_id)
                ->where('tenant_id', Auth::id())
                ->exists();
        }
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.tenant-maintenance-detail');
    }
}
