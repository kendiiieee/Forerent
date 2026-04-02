<?php

namespace App\Livewire\Layouts\Maintenance;

use App\Models\Notification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Carbon\Carbon;

class TenantMaintenanceCreate extends Component
{
    public $problem   = '';
    public $urgency   = 'Level 2';
    public $category  = 'Plumbing';

    protected $rules = [
        'problem'  => 'required|string|min:10|max:1000',
        'urgency'  => 'required|in:Level 1,Level 2,Level 3,Level 4',
        'category' => 'required|in:Plumbing,Electrical,Structural,Appliance,Pest Control',
    ];

    #[On('reset-modal')]
    public function resetForm()
    {
        $this->reset(['problem', 'urgency', 'category']);
        $this->urgency  = 'Level 2';
        $this->category = 'Plumbing';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        $activeLease = DB::table('leases')
            ->where('tenant_id', $user->user_id)
            ->where('status', 'Active')
            ->first();

        if (!$activeLease) {
            $this->addError('problem', 'You do not have an active lease to submit a request.');
            return;
        }

        $latestId     = DB::table('maintenance_requests')->max('request_id') ?? 0;
        $ticketNumber = 'MR-' . str_pad($latestId + 1, 4, '0', STR_PAD_LEFT);

        DB::table('maintenance_requests')->insert([
            'lease_id'      => $activeLease->lease_id,
            'problem'       => $this->problem,
            'urgency'       => $this->urgency,
            'category'      => $this->category,
            'status'        => 'Pending',
            'ticket_number' => $ticketNumber,
            'logged_by'     => $user->first_name . ' ' . $user->last_name,
            'log_date'      => Carbon::now()->format('Y-m-d'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Notify the manager of this unit
        $unit = DB::table('leases')
            ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
            ->join('units', 'beds.unit_id', '=', 'units.unit_id')
            ->where('leases.lease_id', $activeLease->lease_id)
            ->select('units.manager_id')
            ->first();

        if ($unit && $unit->manager_id) {
            Notification::create([
                'user_id' => $unit->manager_id,
                'type' => 'maintenance_request',
                'title' => 'New Maintenance Request',
                'message' => $user->first_name . ' ' . $user->last_name . ' submitted a maintenance request (' . $ticketNumber . '): ' . \Illuminate\Support\Str::limit($this->problem, 80),
                'link' => route('manager.maintenance'),
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal');
        $this->dispatch('refresh-maintenance-list');
    }

    public function render()
    {
        return view('livewire.layouts.maintenance.tenant-maintenance-create');
    }
}
