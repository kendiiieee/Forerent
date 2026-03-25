<?php

namespace App\Livewire\Layouts\Dashboard;

use App\Models\Announcement;
use App\Models\Lease;
use App\Models\Unit;
use Livewire\Component;
use Carbon\Carbon;

class CalendarWidget extends Component
{
    public $role = 'tenant';
    public $currentMonth;
    public $currentYear;
    public $calendarDays = [];
    public $selectedDate;
    public $dailyAnnouncements = [];

    public $announcementDates = [];

    public function mount()
    {
        $this->selectedDate = Carbon::now();
        $this->updateCalendar();
    }

    public function updateCalendar()
    {
        $date = Carbon::parse($this->selectedDate);
        $this->currentMonth = $date->format('F Y');
        $this->currentYear = $date->format('Y');

        // Generate days for the grid
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Empty slots before the 1st of the month
        $emptySlots = $startOfMonth->dayOfWeekIso - 1;
        $this->calendarDays = array_fill(0, $emptySlots, null);

        // Actual days
        for ($i = 1; $i <= $endOfMonth->day; $i++) {
            $this->calendarDays[] = $i;
        }


        $this->loadDailyAnnouncements();

        $this->loadEventDates();
    }

    public function selectDate($date)
    {
        $this->selectedDate = Carbon::parse($date);
        $this->updateCalendar();
    }

    public function previousMonth()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subMonth()->startOfMonth();
        $this->updateCalendar();
    }

    public function nextMonth()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addMonth()->startOfMonth();
        $this->updateCalendar();
    }

    public function currentMonth()
    {
        $this->selectedDate = Carbon::now()->startOfDay();
        $this->updateCalendar();
    }

    public function loadDailyAnnouncements()
    {
        $this->role = auth()->user()->role;

        if ($this->role == "landlord") {
            $this->dailyAnnouncements = Announcement::where('author_id', auth()->id())
                ->whereDate('notification_date', $this->selectedDate)
                ->orderBy('notification_date', 'desc')
                ->get();
        }
        else if ($this->role == "manager") {
            $propertyIds = Unit::where('manager_id', auth()->id())->get()
                ->pluck('property_id')
                ->unique();

            $this->dailyAnnouncements = Announcement::where(function ($query) use ($propertyIds) {
                    $query->where('author_id', auth()->id())
                        ->orWhere(function ($propertyQuery) use ($propertyIds) {
                            $propertyQuery->whereIn('property_id', $propertyIds)
                                ->where('recipient_role', 'manager');
                        });
                })
                ->whereDate('notification_date', $this->selectedDate)
                ->orderBy('notification_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('announcement_id')
                ->values();
        }
        else if ($this->role == "tenant") {
            $leases = Lease::with('bed.unit')->where('tenant_id', auth()->id())->get();

            $propertyIds = $leases->pluck('bed.unit.property_id')->unique();

            $this->dailyAnnouncements = Announcement::whereIn('property_id', $propertyIds)
                ->where('recipient_role', 'tenant')
                ->whereDate('notification_date', $this->selectedDate)
                ->orderBy('notification_date', 'desc')
                ->get();
        }
    }

    public function loadEventDates()
    {
        $this->role = auth()->user()->role;
        $userId = auth()->id();

        // Get the start and end of the currently displayed month
        $startOfMonth = Carbon::parse($this->currentYear . '-' . date('m', strtotime($this->currentMonth)) . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        if ($this->role == "landlord") {
            $this->announcementDates = Announcement::where('author_id', $userId)
                ->whereBetween('notification_date', [$startOfMonth, $endOfMonth])
                ->pluck('notification_date')
                ->map(fn($d) => (int) Carbon::parse($d)->format('d'))
                ->unique()
                ->toArray();

        } elseif ($this->role == "manager") {
            $propertyIds = Unit::where('manager_id', $userId)
                ->pluck('property_id')
                ->unique();

            $this->announcementDates = Announcement::where(function($query) use ($userId, $propertyIds) {
                $query->where('author_id', $userId)
                    ->orWhereIn('property_id', $propertyIds);
            })
                ->where('recipient_role', 'manager')
                ->whereBetween('notification_date', [$startOfMonth, $endOfMonth])
                ->pluck('notification_date')
                ->map(fn($d) => (int) Carbon::parse($d)->format('d'))
                ->unique()
                ->toArray();

        } elseif ($this->role == "tenant") {
            $leases = Lease::with('bed.unit')->where('tenant_id', auth()->id())->get();

            $propertyIds = $leases->pluck('bed.unit.property_id')->unique();

            $this->announcementDates = Announcement::whereIn('property_id', $propertyIds)
                ->where('recipient_role', 'tenant')
                ->whereBetween('notification_date', [$startOfMonth, $endOfMonth])
                ->pluck('notification_date')
                ->map(fn($d) => (int) Carbon::parse($d)->format('d'))
                ->unique()
                ->toArray();
        }
    }

    public function render()
    {
        return view('livewire.layouts.dashboard.calendar-widget');
    }
}
