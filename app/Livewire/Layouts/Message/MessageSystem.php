<?php

namespace App\Livewire\Layouts\Message;

use App\Events\MessageSent;
use App\Models\Lease;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpParser\Node\Stmt\If_;

class MessageSystem extends Component
{
    use WithFileUploads;

    public $activeTab = '';
    public $allowedTabs = [];
    public $tabLabels = [];

    public $search = '';
    public $selectedUserId = null;
    public $messageInput = '';
    public $showProfile = false;
    public $attachment = null;
    public $mediaTab = 'images'; // 'images' or 'documents'

    public function mount()
    {
        $userRole = Auth::user()->role;

        // Role-based chat restrictions:
        // - landlord (owner): can chat with other owners and managers ONLY
        // - manager: can chat with owners, managers, and tenants
        // - tenant: can only chat with managers
        match ($userRole) {
            'landlord' => $this->allowedTabs = ['manager'],
            'manager'  => $this->allowedTabs = ['landlord', 'manager', 'tenant'],
            'tenant'   => $this->allowedTabs = ['manager'],
            default    => $this->allowedTabs = ['manager'],
        };

        $this->tabLabels = [
            'landlord' => 'Owner',
            'manager'  => 'Manager',
            'tenant'   => 'Tenant',
        ];

        $this->activeTab = $this->allowedTabs[0];

        // Auto-select first chat
        $firstChat = $this->getChatsProperty()->first();
        if ($firstChat) {
            $this->selectedUserId = $firstChat->user_id;
        }
    }

    public function setTab($tabName)
    {
        if (in_array($tabName, $this->allowedTabs)) {
            $this->activeTab = $tabName;
            $this->selectedUserId = null;
            $this->showProfile = false;
        }
    }

    public function getChatsProperty()
    {
        $myId = Auth::id();
        $myRole = Auth::user()->role;

        $usersQuery = User::where('user_id', '!=', $myId)
            ->where('role', $this->activeTab)
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            });

        If($myRole === 'manager' && $this->activeTab === 'tenant') {
            $usersQuery->whereHas('leases.bed.unit', function ($q)  use ($myId) {
                $q->where('manager_id', $myId);
            });
        }

        Else If($myRole === 'tenant' && $this->activeTab === 'manager') {
            $usersQuery->whereHas('managedUnits.beds.leases', function ($q) use ($myId) {
                $q->where('tenant_id', $myId);
            });
        }

        $users = $usersQuery->get();

        return $users->map(function ($user) use ($myId) {
            $lastMsg = Message::where(function ($q) use ($myId, $user) {
                $q->where('sender_id', $myId)->where('receiver_id', $user->user_id);
            })->orWhere(function ($q) use ($myId, $user) {
                $q->where('sender_id', $user->user_id)->where('receiver_id', $myId);
            })->latest()->first();

            $user->last_message = $lastMsg
                ? ($lastMsg->type === 'file' ? '📎 ' . $lastMsg->message : $lastMsg->message)
                : 'No messages yet';

            $user->last_time = $lastMsg
                ? $this->formatChatTime($lastMsg->created_at)
                : '';

            $user->last_message_at = $lastMsg ? $lastMsg->created_at : null;

            $user->unread_count = Message::where('sender_id', $user->user_id)
                ->where('receiver_id', $myId)
                ->where('is_read', false)
                ->count();

            return $user;
        })->sortByDesc('last_message_at')->values();
    }

    private function formatChatTime(Carbon $time): string
    {
        if ($time->isToday()) return $time->format('g:i A');
        if ($time->isYesterday()) return 'Yesterday';
        if ($time->greaterThan(now()->subDays(7))) return $time->format('D'); // Mon, Tue...
        return $time->format('d/m/y');
    }

    public function selectChat($userId)
    {
        $this->selectedUserId = $userId;
        $this->showProfile = false;
        $this->attachment = null;

        $this->markAsRead();
    }

    public function sendMessage()
    {
        if (!$this->selectedUserId) return;

        $myId = Auth::id();
        $sent = false;

        // Handle file attachment
        if ($this->attachment) {
            $this->validate(['attachment' => 'file|max:10240']);

            $path = $this->attachment->store('messages', 'public');
            $mimeType = $this->attachment->getMimeType();
            $isImage = str_starts_with($mimeType, 'image/');

            $msg = Message::create([
                'sender_id'  => $myId,
                'receiver_id' => $this->selectedUserId,
                'message'    => $this->attachment->getClientOriginalName(),
                'type'       => 'file',
                'file_path'  => $path,
                'file_type'  => $isImage ? 'image' : 'document',
                'is_read'    => false,
            ]);

            broadcast(new MessageSent($msg))->toOthers();

            $this->attachment = null;
            $sent = true;
        }

        // Handle text message
        if (trim($this->messageInput) !== '') {
            Message::create([
                'sender_id'  => $myId,
                'receiver_id' => $this->selectedUserId,
                'message'    => trim($this->messageInput),
                'type'       => 'text',
                'is_read'    => false,
            ]);

            $this->messageInput = '';
            $sent = true;
        }

        if ($sent) {
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function toggleProfile()
    {
        $this->showProfile = !$this->showProfile;
    }

    public function setMediaTab($tab)
    {
        $this->mediaTab = $tab;
    }

    public function markAsRead()
    {
        if (!$this->selectedUserId) return;

        Message::where('sender_id', $this->selectedUserId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function render()
    {
        $myId = Auth::id();

        // Conversation messages
        $activeMessages = collect();
        $groupedMessages = collect();
        $mediaImages = collect();
        $mediaDocuments = collect();
        $activeChatUser = null;

        if ($this->selectedUserId) {
            $activeChatUser = User::find($this->selectedUserId);

            $activeMessages = Message::where(function ($q) use ($myId) {
                $q->where('sender_id', $myId)->where('receiver_id', $this->selectedUserId);
            })->orWhere(function ($q) use ($myId) {
                $q->where('sender_id', $this->selectedUserId)->where('receiver_id', $myId);
            })->orderBy('created_at', 'asc')->get();

            // Group by date for date separators
            $groupedMessages = $activeMessages->groupBy(function ($msg) {
                return $msg->created_at->format('Y-m-d');
            });

            // Media files shared in this conversation
            $allMedia = Message::where('type', 'file')
                ->where(function ($q) use ($myId) {
                    $q->where(function ($inner) use ($myId) {
                        $inner->where('sender_id', $myId)->where('receiver_id', $this->selectedUserId);
                    })->orWhere(function ($inner) use ($myId) {
                        $inner->where('sender_id', $this->selectedUserId)->where('receiver_id', $myId);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $mediaImages    = $allMedia->where('file_type', 'image')->values();
            $mediaDocuments = $allMedia->where('file_type', 'document')->values();
        }

        return view('livewire.layouts.message.message-system', [
            'chats'           => $this->getChatsProperty(),
            'activeMessages'  => $activeMessages,
            'groupedMessages' => $groupedMessages,
            'activeChatUser'  => $activeChatUser,
            'mediaImages'     => $mediaImages,
            'mediaDocuments'  => $mediaDocuments,
        ]);
    }
}
