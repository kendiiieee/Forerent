<?php

namespace App\Livewire\Layouts\Message;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FloatingChat extends Component
{
    public $isOpen = false;
    public $selectedUserId = null;
    public $messageInput = '';
    public $search = '';
    public $showConcerns = false;

    /**
     * Predefined concern topics with automated replies.
     */
    /**
     * Concern topic categories for grouping in the UI.
     */
    public static array $concernCategories = [
        'High-Priority & Safety' => ['safety_security', 'lockout_assist', 'emergency_contact'],
        'Living Experience'      => ['maintenance', 'noise_complaint', 'parking_issue', 'amenity_booking', 'trash_hygiene'],
        'Billing & Lease'        => ['billing', 'lease_inquiry', 'renewal_inquiry'],
        'Logistics'              => ['move_out_notice', 'pet_registration', 'guest_access'],
        'Other'                  => ['general'],
    ];

    /**
     * Predefined concern topics with automated replies.
     */
    public static array $concernTopics = [
        // === High-Priority & Safety ===
        'safety_security' => [
            'label'   => 'Safety & Security',
            'icon'    => '🛡️',
            'message' => 'I want to report a safety or security concern.',
            'reply'   => "If this is a <strong>life-threatening emergency</strong>, please dial <strong>911</strong> immediately.\n\nTo report suspicious activity to building security, please provide the following details:\n• <strong>Location</strong> of the incident\n• <strong>Description</strong> of what you observed\n• <strong>Time</strong> it occurred\n\nA property manager will review your report and take appropriate action.",
        ],
        'lockout_assist' => [
            'label'   => 'Lockout Assist',
            'icon'    => '🔑',
            'message' => 'I am locked out of my unit.',
            'reply'   => "We understand being locked out can be stressful! 🔑\n\nPlease contact the <strong>on-site security or property management office</strong> for assistance. Note that a <strong>service fee</strong> may apply for after-hours lockout assistance.\n\nPlease provide:\n• Your <strong>unit number</strong>\n• Your <strong>full name</strong> for verification\n\nA manager will respond to assist you as soon as possible.",
        ],
        'emergency_contact' => [
            'label'   => 'Emergency Contact',
            'icon'    => '🚨',
            'message' => 'I need emergency contact information.',
            'reply'   => "For emergencies, please use the following contacts: 🚨\n\n• <strong>Life-threatening emergency</strong>: Dial 911\n• <strong>Fire emergency</strong>: Dial 911 and evacuate the building\n• <strong>Building security</strong>: Contact your property management office\n• <strong>Maintenance emergency</strong> (water leak, gas smell, electrical hazard): Submit an urgent maintenance request via your <strong>Maintenance</strong> page\n\nA property manager will follow up on your concern shortly.",
        ],

        // === Living Experience ===
        'maintenance' => [
            'label'   => 'Maintenance Request',
            'icon'    => '🔧',
            'message' => 'I have a maintenance concern.',
            'reply'   => "Thank you for reaching out about a maintenance issue! 🔧\n\nTo submit a formal maintenance request, please go to your <strong>Maintenance</strong> page from the sidebar menu. There you can:\n• Describe the issue in detail\n• Upload photos of the problem\n• Track the status of your request\n\nIf it's an emergency (water leak, electrical hazard, etc.), please call the property management office directly.",
        ],
        'noise_complaint' => [
            'label'   => 'Noise Complaint',
            'icon'    => '🔊',
            'message' => 'I would like to report a noise complaint.',
            'reply'   => "We're sorry to hear about the noise disturbance. 🔊\n\nYour complaint has been noted. Here's what happens next:\n• A property manager will review your complaint\n• The concerned party will be notified\n• If the issue persists, further action will be taken per the lease agreement\n\nPlease provide details:\n• <strong>What type of noise?</strong>\n• <strong>Which unit/area?</strong>\n• <strong>What time did it occur?</strong>",
        ],
        'parking_issue' => [
            'label'   => 'Parking Issue',
            'icon'    => '🅿️',
            'message' => 'I want to report a parking issue.',
            'reply'   => "Is someone in your assigned parking spot? 🅿️\n\nPlease provide the following so we can take action:\n• A <strong>photo of the vehicle</strong>\n• The <strong>license plate number</strong>\n• Your <strong>assigned spot number</strong>\n\nOur team will attempt to contact the vehicle owner or dispatch towing if necessary. A manager will follow up shortly.",
        ],
        'amenity_booking' => [
            'label'   => 'Amenity Booking',
            'icon'    => '🏊',
            'message' => 'I want to book or ask about an amenity.',
            'reply'   => "Thank you for your interest in our amenities! 🏊\n\nTo book a shared space (gym, pool, lounge, clubhouse), please check with your property manager for availability and booking procedures.\n\nPlease note:\n• A <strong>refundable deposit</strong> may be required for certain reservations\n• Please follow all <strong>posted rules</strong> for shared spaces\n• Report any <strong>damaged equipment</strong> to management immediately\n\nA manager will assist you with your booking shortly.",
        ],
        'trash_hygiene' => [
            'label'   => 'Trash & Hygiene',
            'icon'    => '🗑️',
            'message' => 'I want to report a trash or hygiene issue.',
            'reply'   => "Thank you for helping keep our community clean! 🗑️\n\nPlease let us know:\n• <strong>Location</strong> of the issue (hallway, parking area, trash room, etc.)\n• <strong>Description</strong> of the problem (missed pickup, overflowing bins, messy common area)\n\nReminder: Trash collection occurs on scheduled days. Please ensure all cardboard is broken down and placed in the designated bins.\n\nA manager will address this concern promptly.",
        ],

        // === Billing & Lease ===
        'billing' => [
            'label'   => 'Billing & Payment',
            'icon'    => '💳',
            'message' => 'I have a question about billing or payment.',
            'reply'   => "Thank you for your billing inquiry! 💳\n\nYou can view your payment history and upcoming dues on your <strong>Payment</strong> page. Common questions:\n• <strong>Payment due date</strong>: Rent is typically due on the 1st of each month\n• <strong>Payment methods</strong>: Check your Payment page for available options\n• <strong>Payment receipt</strong>: Available in your payment history after each transaction\n\nIf you need further assistance, a manager will respond to this chat shortly.",
        ],
        'lease_inquiry' => [
            'label'   => 'Lease & Contract',
            'icon'    => '📄',
            'message' => 'I have a question about my lease or contract.',
            'reply'   => "Thank you for your lease inquiry! 📄\n\nYou can view your lease details in your <strong>Settings</strong> page under property details. Common topics:\n• <strong>Lease renewal</strong>: Contact your manager at least 30 days before expiry\n• <strong>Lease terms</strong>: Review your move-in contract for specific terms\n• <strong>Early termination</strong>: Please discuss directly with your property manager\n\nA manager will follow up on your specific question shortly.",
        ],
        'renewal_inquiry' => [
            'label'   => 'Renewal Inquiry',
            'icon'    => '🔄',
            'message' => 'I am interested in renewing my lease.',
            'reply'   => "Interested in staying another year? We'd love to have you! 🔄\n\nOur team will send renewal offers before your lease ends. Here's what you should know:\n• <strong>Renewal notices</strong> are typically sent 30-60 days before lease expiry\n• You can view your current lease end date in your <strong>Settings</strong> page\n• If you'd like to discuss <strong>current market rates</strong> for your unit type, let us know\n\nA manager will follow up with renewal details shortly.",
        ],

        // === Logistics ===
        'move_out_notice' => [
            'label'   => 'Move-Out Notice',
            'icon'    => '📦',
            'message' => 'I want to start the move-out process.',
            'reply'   => "We're sorry to see you go! 📦\n\nTo officially start the move-out process, please provide:\n• Your <strong>intended move-out date</strong>\n• Your <strong>forwarding address</strong> for deposit refund\n\nReminders:\n• Please ensure your unit is cleaned and all personal belongings are removed\n• Return all <strong>keys and access devices</strong> to the management office\n• A <strong>move-out inspection</strong> will be scheduled before your departure\n\nA manager will guide you through the remaining steps.",
        ],
        'pet_registration' => [
            'label'   => 'Pet Registration',
            'icon'    => '🐾',
            'message' => 'I have a question about pet registration or a pet-related issue.',
            'reply'   => "Thank you for reaching out about a pet matter! 🐾\n\nIf you'd like to <strong>register a new pet</strong>, please provide:\n• <strong>Pet type and breed</strong>\n• <strong>Weight</strong>\n• <strong>Vaccination records</strong>\n\nTo <strong>report a pet-related issue</strong> (unleashed pets, excessive noise, waste), please provide the location and, if possible, a photo for our records.\n\nA manager will assist you shortly.",
        ],
        'guest_access' => [
            'label'   => 'Guest Access',
            'icon'    => '🚗',
            'message' => 'I need guest access or visitor parking.',
            'reply'   => "Need to register a guest? 🚗\n\nTo request a <strong>temporary gate code</strong> or <strong>visitor parking pass</strong>, please provide:\n• <strong>Guest's full name</strong>\n• <strong>Vehicle license plate number</strong> (if applicable)\n• <strong>Duration of stay</strong>\n\nPlease note that visitor parking is subject to availability and community rules.\n\nA manager will process your request shortly.",
        ],

        // === Other ===
        'general' => [
            'label'   => 'General Inquiry',
            'icon'    => '💬',
            'message' => 'I have a general question.',
            'reply'   => "Thank you for reaching out! 💬\n\nA property manager will respond to your message as soon as possible. In the meantime, you can also check:\n• <strong>Dashboard</strong> for announcements and updates\n• <strong>Settings</strong> for your property and account details\n\nPlease describe your concern below and we'll get back to you shortly.",
        ],
    ];

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;

        if (!$this->isOpen) {
            $this->clearTyping();
            $this->selectedUserId = null;
            $this->messageInput = '';
        }
    }

    public function selectChat($userId)
    {
        $this->selectedUserId = $userId;
        $this->showConcerns = false;
        $this->markAsDelivered($userId);
        $this->markAsRead($userId);

        // Show concern topics for tenants if no messages exist yet
        if (Auth::user()->role === 'tenant') {
            $hasMessages = Message::where(function ($q) use ($userId) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->where('receiver_id', Auth::id());
            })->exists();

            $this->showConcerns = !$hasMessages;
        }

        $this->dispatch('floating-chat-scroll');
    }

    /**
     * Handle when a tenant selects a concern topic.
     */
    public function selectConcern(string $topicKey)
    {
        if (!$this->selectedUserId || !isset(self::$concernTopics[$topicKey])) {
            return;
        }

        $topic = self::$concernTopics[$topicKey];
        $myId = Auth::id();

        // Send the tenant's concern as their message
        Message::create([
            'sender_id'   => $myId,
            'receiver_id' => $this->selectedUserId,
            'message'     => $topic['message'],
            'type'        => 'text',
            'is_read'     => false,
        ]);

        // Send the automated reply as if from the manager
        Message::create([
            'sender_id'     => $this->selectedUserId,
            'receiver_id'   => $myId,
            'message'       => $topic['reply'],
            'type'          => 'text',
            'is_read'       => true,
            'is_auto_reply' => true,
        ]);

        $this->showConcerns = false;
        $this->dispatch('floating-chat-scroll');
    }

    /**
     * Show the concern topics list again.
     */
    public function showConcernTopics()
    {
        $this->showConcerns = true;
    }

    public function backToList()
    {
        $this->clearTyping();
        $this->selectedUserId = null;
        $this->messageInput = '';
        $this->showConcerns = false;
    }

    // Called when user types in the input field
    public function updatedMessageInput()
    {
        if ($this->selectedUserId && trim($this->messageInput) !== '') {
            Cache::put(
                'typing.' . Auth::id() . '.' . $this->selectedUserId,
                true,
                now()->addSeconds(3)
            );
        } else {
            $this->clearTyping();
        }
    }

    private function clearTyping()
    {
        if ($this->selectedUserId) {
            Cache::forget('typing.' . Auth::id() . '.' . $this->selectedUserId);
        }
    }

    public function sendMessage()
    {
        if (!$this->selectedUserId || trim($this->messageInput) === '') {
            return;
        }

        Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $this->selectedUserId,
            'message'     => trim($this->messageInput),
            'type'        => 'text',
            'is_read'     => false,
        ]);

        $this->messageInput = '';
        $this->clearTyping();
        $this->dispatch('floating-chat-scroll');
    }

    public function getTotalUnreadProperty()
    {
        return Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    private function hasStatusColumns(): bool
    {
        return Cache::remember('messages_has_status_columns', 60, function () {
            return Schema::hasColumn('messages', 'delivered_at');
        });
    }

    /**
     * Mark all undelivered messages from a user as delivered.
     */
    private function markAsDelivered($senderId)
    {
        if (!$this->hasStatusColumns()) {
            return;
        }

        Message::where('sender_id', $senderId)
            ->where('receiver_id', Auth::id())
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);
    }

    /**
     * Mark all unread messages from a user as read.
     */
    private function markAsRead($senderId)
    {
        $query = Message::where('sender_id', $senderId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false);

        if ($this->hasStatusColumns()) {
            $query->update([
                'is_read' => true,
                'read_at' => now(),
                'delivered_at' => \DB::raw('COALESCE(delivered_at, NOW())'),
            ]);
        } else {
            $query->update(['is_read' => true]);
        }
    }

    /**
     * Check if the other user is currently typing.
     */
    private function isOtherUserTyping(): bool
    {
        if (!$this->selectedUserId) {
            return false;
        }

        return Cache::get('typing.' . $this->selectedUserId . '.' . Auth::id(), false);
    }

    private function getChatContacts()
    {
        $myId = Auth::id();
        $myRole = Auth::user()->role;

        $allowedRoles = match ($myRole) {
            'landlord' => ['manager'],
            'manager'  => ['landlord', 'manager', 'tenant'],
            'tenant'   => ['manager'],
            default    => ['manager'],
        };

        $usersQuery = User::where('user_id', '!=', $myId)
            ->whereIn('role', $allowedRoles);

        if ($this->search) {
            $usersQuery->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        }

        if ($myRole === 'manager') {
            $usersQuery->where(function ($q) use ($myId) {
                $q->where('role', '!=', 'tenant')
                    ->orWhereHas('leases.bed.unit', function ($uq) use ($myId) {
                        $uq->where('manager_id', $myId);
                    });
            });
        } elseif ($myRole === 'tenant') {
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

            $user->last_time = $lastMsg ? $this->formatTime($lastMsg->created_at) : '';
            $user->last_message_at = $lastMsg ? $lastMsg->created_at : null;

            $user->unread_count = Message::where('sender_id', $user->user_id)
                ->where('receiver_id', $myId)
                ->where('is_read', false)
                ->count();

            return $user;
        })->sortByDesc('last_message_at')->values();
    }

    private function formatTime(Carbon $time): string
    {
        if ($time->isToday()) return $time->format('g:i A');
        if ($time->isYesterday()) return 'Yesterday';
        if ($time->greaterThan(now()->subDays(7))) return $time->format('D');
        return $time->format('d/m/y');
    }

    public function render()
    {
        $myId = Auth::id();
        $contacts = $this->getChatContacts();
        $messages = collect();
        $chatUser = null;
        $isTyping = false;

        if ($this->selectedUserId) {
            $chatUser = User::find($this->selectedUserId);

            // Mark incoming messages as delivered when viewing the chat
            $this->markAsDelivered($this->selectedUserId);
            // Mark as read
            $this->markAsRead($this->selectedUserId);

            $messages = Message::where(function ($q) use ($myId) {
                $q->where('sender_id', $myId)->where('receiver_id', $this->selectedUserId);
            })->orWhere(function ($q) use ($myId) {
                $q->where('sender_id', $this->selectedUserId)->where('receiver_id', $myId);
            })->orderBy('created_at', 'asc')->get();

            $isTyping = $this->isOtherUserTyping();
        }

        return view('livewire.layouts.message.floating-chat', [
            'contacts'            => $contacts,
            'messages'            => $messages,
            'chatUser'            => $chatUser,
            'totalUnread'         => $this->getTotalUnreadProperty(),
            'isTyping'            => $isTyping,
            'concernTopics'       => self::$concernTopics,
            'concernCategories'   => self::$concernCategories,
        ]);
    }
}
