<?php

namespace App\Livewire\Navbars;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public $showDropdown = false;
    public $notifications = [];
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    #[On('notification-created')]
    public function loadNotifications()
    {
        $user = Auth::user();
        if (!$user) return;

        $this->notifications = Notification::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->toArray();

        $this->unreadCount = Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->count();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::where('notification_id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
            $this->loadNotifications();

            if ($notification->link) {
                return $this->redirect($notification->link);
            }
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.navbars.notification-bell');
    }
}
