<?php

namespace App\Livewire\Layouts\Dashboard;

use App\Models\Announcement;
use App\Models\Lease;
use App\Models\Unit;
use Livewire\Component;

class AnnouncementList extends Component
{
    public $announcements = [];
    public $role = "tenant";

    public function mount()
    {
        $this->role = auth()->user()->role;

        if ($this->role == "landlord") {
            $this->announcements = Announcement::where('author_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();
        }
        else if ($this->role == "manager") {
            $propertyIds = Unit::where('manager_id', auth()->id())->get()
            ->pluck('property_id')
            ->unique();

            $this->announcements = Announcement::where(function ($query) use ($propertyIds) {
                    $query->where('author_id', auth()->id())
                        ->orWhere(function ($propertyQuery) use ($propertyIds) {
                            $propertyQuery->whereIn('property_id', $propertyIds)
                                ->where('recipient_role', 'manager');
                        });
                })
                ->orderBy('created_at', 'desc')
                ->distinct('announcement_id')
                ->get();
        }
        else if ($this->role == "tenant") {
            $propertyIds = Lease::where('tenant_id', auth()->id())
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->pluck('units.property_id')
                ->unique();


            $this->announcements = Announcement::whereIn('property_id', $propertyIds)
                ->where('recipient_role', 'tenant')
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.layouts.dashboard.announcement-list');
    }
}
