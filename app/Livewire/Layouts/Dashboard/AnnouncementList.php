<?php

namespace App\Livewire\Layouts\Dashboard;

use App\Models\Announcement;
use App\Models\Lease;
use App\Models\Unit;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AnnouncementList extends Component
{
    public $announcements = [];
    public $role = "tenant";

    public function mount()
    {
        $this->role = Auth::user()->role;
        $this->loadAnnouncements();
    }

    #[On('announcement-posted')]
    public function refreshAnnouncements(): void
    {
        $this->loadAnnouncements();
    }

    private function loadAnnouncements(): void
    {
        $this->role = Auth::user()->role;

        if ($this->role == "landlord") {
            $this->announcements = $this->sortByEarliestUpcomingAnnouncement(
                Announcement::where('author_id', Auth::id())
            )->get();
        }
        else if ($this->role == "manager") {
            $propertyIds = Unit::where('manager_id', Auth::id())->get()
                ->pluck('property_id')
                ->unique();

            $this->announcements = $this->sortByEarliestUpcomingAnnouncement(
                Announcement::where(function ($query) use ($propertyIds) {
                    $query->where('author_id', Auth::id())
                        ->orWhere(function ($propertyQuery) use ($propertyIds) {
                            $propertyQuery->whereIn('property_id', $propertyIds)
                                ->where('recipient_role', 'manager');
                        });
                })
            )
                ->get()
                ->unique('announcement_id')
                ->values();
            }
            else if ($this->role == "tenant") {
            $propertyIds = Lease::where('tenant_id', Auth::id())
                ->join('beds', 'leases.bed_id', '=', 'beds.bed_id')
                ->join('units', 'beds.unit_id', '=', 'units.unit_id')
                ->pluck('units.property_id')
                ->unique();

            $this->announcements = $this->sortByEarliestUpcomingAnnouncement(
                Announcement::whereIn('property_id', $propertyIds)
                    ->where('recipient_role', 'tenant')
            )->get();
        }
    }

    private function sortByEarliestUpcomingAnnouncement($query)
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->orderByRaw(
                'CASE WHEN notification_date IS NULL THEN 2 WHEN DATE(notification_date) >= ? THEN 0 ELSE 1 END ASC',
                [$today]
            )
            ->orderByRaw('CASE WHEN DATE(notification_date) >= ? THEN notification_date END ASC', [$today])
            ->orderByRaw('CASE WHEN DATE(notification_date) < ? THEN notification_date END DESC', [$today])
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.layouts.dashboard.announcement-list');
    }
}
