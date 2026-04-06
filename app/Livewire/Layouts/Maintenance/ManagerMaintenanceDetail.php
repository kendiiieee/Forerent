<?php

namespace App\Livewire\Layouts\Maintenance;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\MaintenanceNote;
use App\Models\MaintenanceActivity;
use App\Models\MaintenanceLog;
use App\Models\BillingItem;
use App\Models\Billing;
use App\Livewire\Concerns\WithNotifications;

class ManagerMaintenanceDetail extends Component
{
    use WithNotifications;
    public $ticket          = null;
    public $ticketIdDisplay = '';
    public $successMessage  = '';
    public $feedback        = null;

    // Cost tracking
    public $showCostForm    = false;
    public $costAmount      = '';
    public $costDescription = '';
    public $chargedTo       = 'owner';
    public $editingCostId   = null;
    public $costItems       = [];
    public $requestTotal    = 0;
    public $unitTotal       = 0;
    public $unitId          = null;

    // Manager notes
    public $noteText        = '';
    public $notes           = [];

    // Activity log
    public $activities      = [];

    // Tracking fields
    public $assignedTo              = '';
    public $expectedCompletionDate  = '';
    public $newUrgency              = '';

    // Cost threshold (PHP) — warning when request total exceeds this
    public const COST_WARNING_THRESHOLD = 10000;

    public function mount(?int $initialRequestId = null): void
    {
        if ($initialRequestId) {
            $this->loadRequest($initialRequestId);
        }
    }

    #[On('managerMaintenanceSelected')]
    public function loadRequest($requestId)
    {
        if ($requestId === null) {
            $this->ticket   = null;
            $this->feedback = null;
            $this->resetCostForm();
            return;
        }

        // Only load non-archived tickets that belong to this manager's units
        $this->ticket = DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('maintenance_requests.request_id', $requestId)
            ->where('units.manager_id', Auth::id())
            ->whereNull('maintenance_requests.deleted_at')
            ->select('maintenance_requests.*')
            ->first();

        if ($this->ticket) {
            $this->ticketIdDisplay = $this->ticket->ticket_number
                ?? 'TKT-' . str_pad($this->ticket->request_id, 4, '0', STR_PAD_LEFT);

            $this->feedback = DB::table('maintenance_feedback')
                ->where('request_id', $this->ticket->request_id)
                ->first();

            $this->loadCostData();
            $this->loadNotes();
            $this->loadActivities();

            // Populate tracking fields
            $this->assignedTo = $this->ticket->assigned_to ?? '';
            $this->expectedCompletionDate = $this->ticket->expected_completion_date ?? '';
            $this->newUrgency = $this->ticket->urgency ?? '';
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
                ->whereNull('maintenance_requests.deleted_at')
                ->sum('maintenance_logs.cost');
        }
    }

    /**
     * Verify the authenticated manager owns the unit tied to the current ticket.
     */
    private function authorizeManagerForTicket(): bool
    {
        if (!$this->ticket) return false;

        return DB::table('maintenance_requests')
            ->join('leases', 'maintenance_requests.lease_id', '=', 'leases.lease_id')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('maintenance_requests.request_id', $this->ticket->request_id)
            ->where('units.manager_id', Auth::id())
            ->exists();
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
            $this->chargedTo = 'owner';
            $this->editingCostId = null;
        }
    }

    public function editCostItem($logId)
    {
        $item = collect($this->costItems)->firstWhere('log_id', $logId);
        if ($item) {
            $this->editingCostId = $logId;
            $this->costDescription = $item['description'] ?? '';
            $this->costAmount = $item['cost'];
            $this->chargedTo = $item['charged_to'] ?? 'owner';
            $this->showCostForm = true;
        }
    }

    /**
     * Save a cost item for this maintenance request.
     */
    public function saveCost()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'costAmount'      => 'required|numeric|min:1|max:999999.99',
            'costDescription' => 'required|string|min:3|max:255',
            'chargedTo'       => 'required|in:owner,tenant',
        ], [
            'costAmount.required'      => 'Please enter a cost amount.',
            'costAmount.numeric'       => 'Amount must be a valid number.',
            'costAmount.min'           => 'Cost must be at least PHP 1.00.',
            'costAmount.max'           => 'Cost cannot exceed PHP 999,999.99.',
            'costDescription.required' => 'Please enter a description.',
            'costDescription.min'      => 'Description must be at least 3 characters.',
            'costDescription.max'      => 'Description cannot exceed 255 characters.',
        ]);

        $amount = round((float) $this->costAmount, 2);
        $chargedLabel = $this->chargedTo === 'tenant' ? 'Tenant' : 'Owner';

        if ($this->editingCostId) {
            // Get the old cost data before updating
            $oldCost = DB::table('maintenance_logs')->where('log_id', $this->editingCostId)->first();
            $oldAmount = $oldCost->cost ?? 0;
            $oldChargedTo = $oldCost->charged_to ?? 'owner';

            DB::table('maintenance_logs')
                ->where('log_id', $this->editingCostId)
                ->update([
                    'cost'            => $amount,
                    'description'     => $this->costDescription,
                    'charged_to'      => $this->chargedTo,
                    'updated_at'      => now(),
                ]);

            // Sync billing if charge-to changed or amount changed
            $ticketNum = $this->ticketIdDisplay;
            if ($oldChargedTo === 'tenant' && $this->chargedTo === 'owner') {
                // Was charged to tenant, now charged to owner — remove billing item
                $this->removeTenantBillingItem($oldAmount, $oldCost->description ?? '');
            } elseif ($oldChargedTo === 'owner' && $this->chargedTo === 'tenant') {
                // Was owner, now tenant — create billing item
                $this->chargeTenantBilling($amount, $this->costDescription);
            } elseif ($oldChargedTo === 'tenant' && $this->chargedTo === 'tenant' && $oldAmount != $amount) {
                // Still tenant but amount changed — update billing
                $this->updateTenantBillingItem($oldAmount, $amount, $this->costDescription);
            }

            $this->logActivity('cost_updated', "Cost updated: PHP " . number_format($amount, 2) . " — {$this->costDescription} (Charged to: {$chargedLabel})");
            $this->notifySuccess('Cost Item Updated', "Cost entry has been updated.");
        } else {
            DB::table('maintenance_logs')->insert([
                'request_id'      => $this->ticket->request_id,
                'completion_date' => now()->toDateString(),
                'cost'            => $amount,
                'description'     => $this->costDescription,
                'charged_to'      => $this->chargedTo,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $this->logActivity('cost_added', "Cost added: PHP " . number_format($amount, 2) . " — {$this->costDescription} (Charged to: {$chargedLabel})");

            $ticketNum = $this->ticketIdDisplay;
            $this->notifyTenant(
                'Maintenance Cost Added',
                "A cost of PHP " . number_format($amount, 2) . " has been logged for your request ({$ticketNum})."
            );

            if ($this->chargedTo === 'tenant') {
                $this->chargeTenantBilling($amount, $this->costDescription);
            }

            $this->notifySuccess('Cost Item Added', "Cost of PHP " . number_format($amount, 2) . " charged to {$chargedLabel}.");
        }

        $this->costAmount = '';
        $this->costDescription = '';
        $this->chargedTo = 'owner';
        $this->editingCostId = null;
        $this->showCostForm = false;

        $this->loadCostData();
        $this->loadActivities();
        $this->dispatch('refreshDashboard');
    }

    /**
     * Create a BillingItem to charge the tenant for a maintenance cost.
     */
    private function chargeTenantBilling(float $amount, string $description): void
    {
        if (!$this->ticket) return;

        // Find the tenant's latest active billing for this lease
        $billing = Billing::where('lease_id', $this->ticket->lease_id)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->orderBy('billing_date', 'desc')
            ->first();

        if (!$billing) return;

        $ticketNum = $this->ticketIdDisplay;

        BillingItem::create([
            'billing_id'      => $billing->billing_id,
            'charge_category' => 'conditional',
            'charge_type'     => 'maintenance',
            'description'     => "Maintenance ({$ticketNum}): {$description}",
            'amount'          => $amount,
        ]);

        // Update billing total
        $billing->update([
            'amount' => $billing->amount + $amount,
            'to_pay' => $billing->to_pay + $amount,
        ]);

        // Notify tenant
        $this->notifyTenant(
            'Maintenance Cost Charged',
            "A maintenance cost of PHP " . number_format($amount, 2) . " for ticket {$ticketNum} has been added to your billing."
        );
    }

    /**
     * Remove a billing item when cost is changed from tenant to owner.
     */
    private function removeTenantBillingItem(float $oldAmount, string $description): void
    {
        if (!$this->ticket) return;

        $billing = Billing::where('lease_id', $this->ticket->lease_id)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->orderBy('billing_date', 'desc')
            ->first();

        if (!$billing) return;

        $ticketNum = $this->ticketIdDisplay;
        $item = BillingItem::where('billing_id', $billing->billing_id)
            ->where('charge_type', 'maintenance')
            ->where('amount', $oldAmount)
            ->where('description', 'like', "%{$ticketNum}%")
            ->first();

        if ($item) {
            $billing->update([
                'amount' => max(0, $billing->amount - $item->amount),
                'to_pay' => max(0, $billing->to_pay - $item->amount),
            ]);
            $item->delete();
        }
    }

    /**
     * Update a billing item when a tenant-charged cost amount changes.
     */
    private function updateTenantBillingItem(float $oldAmount, float $newAmount, string $description): void
    {
        if (!$this->ticket) return;

        $billing = Billing::where('lease_id', $this->ticket->lease_id)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->orderBy('billing_date', 'desc')
            ->first();

        if (!$billing) return;

        $ticketNum = $this->ticketIdDisplay;
        $item = BillingItem::where('billing_id', $billing->billing_id)
            ->where('charge_type', 'maintenance')
            ->where('amount', $oldAmount)
            ->where('description', 'like', "%{$ticketNum}%")
            ->first();

        if ($item) {
            $diff = $newAmount - $oldAmount;
            $item->update([
                'amount'      => $newAmount,
                'description' => "Maintenance ({$ticketNum}): {$description}",
            ]);
            $billing->update([
                'amount' => max(0, $billing->amount + $diff),
                'to_pay' => max(0, $billing->to_pay + $diff),
            ]);
        }
    }

    /**
     * Remove a cost item.
     */
    public function removeCostItem($logId)
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        DB::table('maintenance_logs')
            ->where('log_id', $logId)
            ->update(['deleted_at' => now()]);

        $this->logActivity('cost_removed', 'A cost item was removed.');
        $this->notifySuccess('Cost Item Removed', 'The cost entry has been removed.');
        $this->loadCostData();
        $this->loadActivities();
        $this->dispatch('refreshDashboard');
    }

    /**
     * Send a database notification to the tenant who owns this ticket.
     */
    private function notifyTenant(string $title, string $message): void
    {
        if (!$this->ticket) return;

        $tenantId = DB::table('leases')
            ->where('lease_id', $this->ticket->lease_id)
            ->value('tenant_id');

        if ($tenantId) {
            Notification::create([
                'user_id' => $tenantId,
                'type'    => 'maintenance_update',
                'title'   => $title,
                'message' => $message,
                'link'    => route('tenant.maintenance'),
            ]);
        }
    }

    /**
     * Reset cost form state.
     */
    private function resetCostForm()
    {
        $this->showCostForm = false;
        $this->costAmount = '';
        $this->costDescription = '';
        $this->chargedTo = 'owner';
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
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        // Require Manage Request fields to be filled before marking as Ongoing
        $missingFields = [];
        if (empty($this->ticket->assigned_to) && empty($this->assignedTo)) {
            $missingFields[] = 'Assigned Worker/Vendor';
        }
        if (empty($this->ticket->expected_completion_date) && empty($this->expectedCompletionDate)) {
            $missingFields[] = 'Expected Completion Date';
        }

        if (!empty($missingFields)) {
            $this->dispatch('close-modal', 'confirm-mark-ongoing');
            $this->successMessage = '';
            $this->addError('manageRequest', 'Please fill out the following before marking as Ongoing: ' . implode(', ', $missingFields));
            $this->dispatch('scroll-to-manage-request');
            return;
        }

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'status'     => 'Ongoing',
                'updated_at' => now(),
            ]);

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->logActivity('status_changed', 'Status changed from Pending to Ongoing.');

        $ticketNum = $this->ticketIdDisplay;
        $this->notifyTenant(
            'Maintenance In Progress',
            "Your maintenance request ({$ticketNum}) is now being worked on."
        );

        $this->notifySuccess('Status Updated', 'Request is now marked as Ongoing.');
        $this->loadActivities();

        $this->dispatch('close-modal', 'confirm-mark-ongoing');
        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    /**
     * Manager marks the request as Completed.
     */
    public function markAsCompleted()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        // Require at least one cost item before marking as completed
        if (empty($this->costItems)) {
            $this->dispatch('close-modal', 'confirm-mark-completed');
            $this->addError('costRequired', 'Please add at least one cost entry before marking as completed.');
            $this->dispatch('scroll-to-cost-section');
            return;
        }

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

        $this->logActivity('status_changed', 'Status changed to Completed.');

        $ticketNum = $this->ticketIdDisplay;
        $this->notifyTenant(
            'Maintenance Completed',
            "Your maintenance request ({$ticketNum}) has been marked as completed. You can now submit feedback."
        );

        $this->notifySuccess('Request Completed', 'Maintenance request has been marked as completed.');
        $this->loadActivities();

        $this->dispatch('close-modal', 'confirm-mark-completed');
        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    // ─── MANAGER NOTES ───

    public function loadNotes()
    {
        if (!$this->ticket) return;
        $this->notes = DB::table('maintenance_notes')
            ->join('users', 'maintenance_notes.user_id', '=', 'users.user_id')
            ->where('maintenance_notes.request_id', $this->ticket->request_id)
            ->select('maintenance_notes.*', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as author_name"))
            ->orderBy('maintenance_notes.created_at', 'desc')
            ->get()
            ->map(fn($n) => (array) $n)
            ->toArray();
    }

    public function saveNote()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'noteText' => 'required|string|min:3|max:1000',
        ], [
            'noteText.required' => 'Please enter a note.',
            'noteText.min'      => 'Note must be at least 3 characters.',
        ]);

        MaintenanceNote::create([
            'request_id' => $this->ticket->request_id,
            'user_id'    => Auth::id(),
            'note'       => $this->noteText,
        ]);

        $this->logActivity('note_added', 'Internal note added.');

        $this->noteText = '';
        $this->notifySuccess('Note Added', 'Your internal note has been saved.');
        $this->loadNotes();
    }

    public function deleteNote($noteId)
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        MaintenanceNote::where('note_id', $noteId)
            ->where('user_id', Auth::id())
            ->delete();

        $this->notifySuccess('Note Removed', 'The note has been deleted.');
        $this->loadNotes();
    }

    // ─── ACTIVITY LOG ───

    public function loadActivities()
    {
        if (!$this->ticket) return;
        $this->activities = DB::table('maintenance_activities')
            ->join('users', 'maintenance_activities.user_id', '=', 'users.user_id')
            ->where('maintenance_activities.request_id', $this->ticket->request_id)
            ->select('maintenance_activities.*', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as actor_name"))
            ->orderBy('maintenance_activities.created_at', 'desc')
            ->get()
            ->map(fn($a) => (array) $a)
            ->toArray();
    }

    private function logActivity(string $action, string $details): void
    {
        if (!$this->ticket) return;
        MaintenanceActivity::create([
            'request_id' => $this->ticket->request_id,
            'user_id'    => Auth::id(),
            'action'     => $action,
            'details'    => $details,
        ]);
    }

    // ─── TRACKING FIELDS ───

    public function saveManageRequest()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'newUrgency'             => 'required|in:Level 1,Level 2,Level 3,Level 4',
            'assignedTo'             => 'required|string|max:255',
            'expectedCompletionDate' => 'required|date|after_or_equal:today',
        ], [
            'newUrgency.required'             => 'Please select a priority level.',
            'assignedTo.required'             => 'Please assign a worker or vendor.',
            'expectedCompletionDate.required' => 'Please set an expected completion date.',
            'expectedCompletionDate.after_or_equal' => 'The expected completion date must be today or later.',
        ]);

        $oldUrgency  = $this->ticket->urgency;
        $oldWorker   = $this->ticket->assigned_to ?? 'None';
        $oldDate     = $this->ticket->expected_completion_date;

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'urgency'                  => $this->newUrgency,
                'assigned_to'              => $this->assignedTo,
                'expected_completion_date'  => $this->expectedCompletionDate,
                'updated_at'               => now(),
            ]);

        // Log individual changes
        if ($this->newUrgency !== $oldUrgency) {
            $this->logActivity('urgency_changed', "Priority changed from {$oldUrgency} to {$this->newUrgency}");
            $ticketNum = $this->ticketIdDisplay;
            $this->notifyTenant(
                'Priority Updated',
                "Your maintenance request ({$ticketNum}) priority has been changed to {$this->newUrgency}."
            );
        }

        if ($this->assignedTo !== $oldWorker) {
            $this->logActivity('worker_assigned', "Assigned to: {$this->assignedTo} (was: {$oldWorker})");
            $ticketNum = $this->ticketIdDisplay;
            $this->notifyTenant(
                'Worker Assigned',
                "A worker/vendor ({$this->assignedTo}) has been assigned to your request ({$ticketNum})."
            );
        }

        if ($this->expectedCompletionDate !== $oldDate) {
            $this->logActivity('eta_updated', "Expected completion date set to: {$this->expectedCompletionDate}");
            $ticketNum = $this->ticketIdDisplay;
            $formattedDate = \Carbon\Carbon::parse($this->expectedCompletionDate)->format('M d, Y');
            $this->notifyTenant(
                'Completion Date Updated',
                "Your maintenance request ({$ticketNum}) is expected to be completed by {$formattedDate}."
            );
        }

        $this->ticket = DB::table('maintenance_requests')->where('request_id', $this->ticket->request_id)->first();
        $this->loadActivities();
        $this->dispatch('manage-request-saved');
        $this->notifySuccess('Details Updated', 'Manage request details have been saved.');
    }

    public function saveAssignedTo()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate(['assignedTo' => 'nullable|string|max:255']);

        $old = $this->ticket->assigned_to ?? 'None';
        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update(['assigned_to' => $this->assignedTo ?: null, 'updated_at' => now()]);

        $this->logActivity('worker_assigned', "Assigned to: {$this->assignedTo} (was: {$old})");
        $this->ticket = DB::table('maintenance_requests')->where('request_id', $this->ticket->request_id)->first();
        $this->notifySuccess('Assignment Updated', 'Worker/vendor has been updated.');
    }

    public function saveExpectedDate()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate(['expectedCompletionDate' => 'nullable|date|after_or_equal:today']);

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update(['expected_completion_date' => $this->expectedCompletionDate ?: null, 'updated_at' => now()]);

        $this->logActivity('eta_updated', "Expected completion date set to: " . ($this->expectedCompletionDate ?: 'Not set'));
        $this->ticket = DB::table('maintenance_requests')->where('request_id', $this->ticket->request_id)->first();
        $this->notifySuccess('Date Updated', 'Expected completion date has been updated.');
    }

    // ─── URGENCY ESCALATION ───

    public function changeUrgency()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate(['newUrgency' => 'required|in:Level 1,Level 2,Level 3,Level 4']);

        if ($this->newUrgency === $this->ticket->urgency) return;

        $old = $this->ticket->urgency;
        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update(['urgency' => $this->newUrgency, 'updated_at' => now()]);

        $this->logActivity('urgency_changed', "Priority changed from {$old} to {$this->newUrgency}");

        $ticketNum = $this->ticketIdDisplay;
        $this->notifyTenant(
            'Priority Updated',
            "Your maintenance request ({$ticketNum}) priority has been changed to {$this->newUrgency}."
        );

        $this->ticket = DB::table('maintenance_requests')->where('request_id', $this->ticket->request_id)->first();
        $this->notifySuccess('Priority Updated', "Urgency changed to {$this->newUrgency}.");
    }

    // ─── ARCHIVE / DELETE ───

    public function archiveRequest()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $this->logActivity('archived', 'Request archived by manager.');

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update(['deleted_at' => now()]);

        $this->ticket = null;
        $this->feedback = null;
        $this->resetCostForm();
        $this->notes = [];
        $this->activities = [];

        $this->notifySuccess('Request Archived', 'The maintenance request has been archived.');
        $this->dispatch('close-modal', 'confirm-archive-request');
        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    /**
     * Revert the request to its previous status.
     * Completed → Ongoing, Ongoing → Pending.
     */
    public function revertStatus()
    {
        if (!$this->authorizeManagerForTicket()) {
            abort(403, 'Unauthorized action.');
        }

        $previousStatus = match ($this->ticket->status) {
            'Completed' => 'Ongoing',
            'Ongoing'   => 'Pending',
            default     => null,
        };

        if (!$previousStatus) return;

        DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->update([
                'status'     => $previousStatus,
                'updated_at' => now(),
            ]);

        $this->ticket = DB::table('maintenance_requests')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->feedback = DB::table('maintenance_feedback')
            ->where('request_id', $this->ticket->request_id)
            ->first();

        $this->logActivity('status_changed', "Status reverted from {$this->ticket->status} to {$previousStatus}.");

        $ticketNum = $this->ticketIdDisplay;
        $this->notifyTenant(
            'Maintenance Status Updated',
            "Your maintenance request ({$ticketNum}) has been moved back to {$previousStatus}."
        );

        $this->notifySuccess('Status Reverted', "Request has been moved back to {$previousStatus}.");
        $this->loadActivities();

        $this->dispatch('close-modal', 'confirm-revert-status');
        $this->dispatch('refresh-maintenance-list');
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.manager-maintenance-detail');
    }
}
