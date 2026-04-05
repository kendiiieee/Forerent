<div wire:poll.3s x-data="{ open: @entangle('isOpen') }">

    {{-- Floating Chat Toggle Button --}}
    <flux:tooltip :content="'Open the messaging panel'" position="bottom">
        <button
            x-show="!open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="scale-50 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-50 opacity-0"
            @click="open = true"
            class="w-14 h-14 rounded-full bg-[#070589] text-white shadow-lg hover:bg-[#0a07b5] transition-all duration-200 flex items-center justify-center relative"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            @if($totalUnread > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1">
                    {{ $totalUnread > 99 ? '99+' : $totalUnread }}
                </span>
            @endif
        </button>
    </flux:tooltip>

    {{-- Chat Window --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        x-cloak
        style="position: absolute; bottom: 0; right: 0; width: 360px; height: 480px; transform-origin: bottom right;"
        class="bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden"
    >

        @if($selectedUserId && $chatUser)
            {{-- ===== CONVERSATION VIEW ===== --}}

            {{-- Chat Header --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 bg-[#070589] text-white">
                <flux:tooltip :content="'Return to your contact list'" position="bottom">
                    <button wire:click="backToList" class="hover:bg-white/20 rounded-full p-1 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </flux:tooltip>
                <img src="{{ $chatUser->profile_image_url }}" class="w-8 h-8 rounded-full object-cover border border-white/30 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate">{{ $chatUser->first_name }} {{ $chatUser->last_name }}</p>
                    @if($isTyping)
                        <p class="text-xs text-emerald-300 italic">typing...</p>
                    @else
                        <p class="text-xs text-white/70 capitalize">{{ $chatUser->role === 'landlord' ? 'Owner' : $chatUser->role }}</p>
                    @endif
                </div>
                <flux:tooltip :content="'Close the messaging panel'" position="bottom">
                    <button @click="open = false" class="hover:bg-white/20 rounded-full p-1 transition flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </flux:tooltip>
            </div>

            {{-- Messages --}}
            <div id="floating-chat-messages" class="flex-1 overflow-y-auto p-4 space-y-1 bg-[#F4F7FC]" style="scrollbar-width: thin;">

                {{-- Concern Topics Selection (Tenant only, shown for new conversations) --}}
                @if($showConcerns && auth()->user()->role === 'tenant')
                    <div class="flex flex-col h-full">
                        <div class="text-center py-3 px-2 flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-[#070589]/10 flex items-center justify-center mx-auto mb-1.5">
                                <svg class="w-5 h-5 text-[#070589]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">How can we help you?</p>
                            <p class="text-[11px] text-slate-400">Select a topic to get started</p>
                        </div>

                        <div class="flex-1 overflow-y-auto px-2 pb-3 space-y-3" style="scrollbar-width: thin;">
                            @foreach($concernCategories as $categoryName => $topicKeys)
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1 mb-1.5">{{ $categoryName }}</p>
                                    <div class="space-y-1.5">
                                        @foreach($topicKeys as $key)
                                            @if(isset($concernTopics[$key]))
                                                <button
                                                    wire:click="selectConcern('{{ $key }}')"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 bg-white rounded-xl border border-slate-200 hover:border-[#070589]/30 hover:bg-[#070589]/5 transition-all duration-200 text-left group"
                                                >
                                                    <span class="text-base flex-shrink-0">{{ $concernTopics[$key]['icon'] }}</span>
                                                    <span class="text-xs font-medium text-slate-700 group-hover:text-[#070589]">{{ $concernTopics[$key]['label'] }}</span>
                                                    <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-[#070589] ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    @php
                        $lastSentIndex = null;
                        foreach ($messages as $i => $m) {
                            if ($m->sender_id === auth()->id()) $lastSentIndex = $i;
                        }
                    @endphp

                    @forelse($messages as $index => $msg)
                        @php
                            $isMine = $msg->sender_id === auth()->id();
                            $isAutoReply = $msg->is_auto_reply ?? false;
                            $isMyAutoReply = $isMine && $isAutoReply;
                        @endphp

                        {{-- Auto-reply banner (manager view) --}}
                        @if($isMyAutoReply)
                            <div class="flex justify-center my-1">
                                <span class="text-[11px] font-medium text-amber-500 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5">Auto-reply sent on your behalf</span>
                            </div>
                        @endif

                        <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%] px-3 py-2 rounded-2xl text-sm
                                @if($isMyAutoReply)
                                    bg-amber-50 text-amber-900 border border-amber-200 rounded-br-md
                                @elseif($isMine)
                                    bg-[#070589] text-white rounded-br-md
                                @else
                                    bg-white text-slate-800 rounded-bl-md shadow-sm
                                @endif
                            ">

                                {{-- Auto-reply badge (tenant view) --}}
                                @if($isAutoReply && !$isMine)
                                    <p class="text-[11px] font-medium text-blue-400 mb-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Automated Reply
                                    </p>
                                @endif

                                @if($msg->type === 'file')
                                    <p class="text-xs opacity-70">📎 {{ $msg->message }}</p>
                                @elseif($isAutoReply)
                                    <p class="whitespace-pre-line">{!! strip_tags($msg->message, '<strong>') !!}</p>
                                @else
                                    <p class="whitespace-pre-line">{{ $msg->message }}</p>
                                @endif
                                <p class="text-[11px] mt-1 {{ $isMine ? 'text-white/60' : 'text-slate-400' }} text-right">
                                    {{ $msg->created_at->format('g:i A') }}
                                </p>
                            </div>
                        </div>

                        {{-- Messenger-style status --}}
                        @if($isMine && $index === $lastSentIndex)
                            <div class="flex justify-end pr-1">
                                @if(($msg->read_at ?? null) || $msg->is_read)
                                    <img src="{{ $chatUser->profile_image_url }}" class="w-4 h-4 rounded-full object-cover" title="Seen by {{ $chatUser->first_name }}">
                                @elseif($msg->delivered_at ?? null)
                                    <span class="text-[11px] text-slate-400">Delivered</span>
                                @else
                                    <span class="text-[11px] text-slate-400">Sent</span>
                                @endif
                            </div>
                        @endif
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <p class="text-sm text-slate-400">No messages yet. Say hello!</p>
                        </div>
                    @endforelse

                    {{-- Quick concern button for tenants (shown after initial conversation) --}}
                    @if(auth()->user()->role === 'tenant' && $messages->count() > 0)
                        <div class="flex justify-center pt-2">
                            <button
                                wire:click="showConcernTopics"
                                class="text-[11px] text-[#070589] hover:text-[#0a07b5] font-medium flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#070589]/5 hover:bg-[#070589]/10 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                Select a concern topic
                            </button>
                        </div>
                    @endif

                    {{-- Typing indicator bubble --}}
                    @if($isTyping)
                        <div class="flex justify-start">
                            <div class="bg-white text-slate-800 rounded-2xl rounded-bl-md shadow-sm px-4 py-3 flex items-center gap-1">
                                <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                                <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                                <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Message Input --}}
            <div class="px-3 py-3 border-t border-slate-100 bg-white">
                <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                    <input
                        type="text"
                        wire:model.live.debounce.500ms="messageInput"
                        placeholder="Type a message..."
                        class="flex-1 bg-[#F4F6FB] border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400"
                        autocomplete="off"
                    >
                    <flux:tooltip :content="'Send your message now'" position="bottom">
                        <button
                            type="submit"
                            class="w-9 h-9 rounded-full bg-[#070589] text-white flex items-center justify-center hover:bg-[#0a07b5] transition flex-shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </flux:tooltip>
                </form>
            </div>

        @else
            {{-- ===== CONTACT LIST VIEW ===== --}}

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-[#070589] text-white">
                <h3 class="text-sm font-bold tracking-wide">MESSAGES</h3>
                <flux:tooltip :content="'Close the messaging panel'" position="bottom">
                    <button @click="open = false" class="hover:bg-white/20 rounded-full p-1 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </flux:tooltip>
            </div>

            {{-- Search --}}
            <div class="px-3 py-2 border-b border-slate-100">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search contacts..."
                        class="w-full bg-[#F4F6FB] border border-slate-200 rounded-xl py-2 pl-3 pr-9 text-xs focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Contact List --}}
            <div class="flex-1 overflow-y-auto" style="scrollbar-width: thin;">
                @forelse($contacts as $contact)
                    <button
                        wire:click="selectChat({{ $contact->user_id }})"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition text-left border-b border-slate-50"
                    >
                        <img src="{{ $contact->profile_image_url }}" class="w-10 h-10 rounded-full object-cover border-2 border-slate-100 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $contact->first_name }} {{ $contact->last_name }}</p>
                                <span class="text-[11px] text-slate-400 flex-shrink-0 ml-2">{{ $contact->last_time }}</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ $contact->last_message }}</p>
                        </div>
                        @if($contact->unread_count > 0)
                            <span class="bg-red-500 text-white text-[11px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 flex-shrink-0">
                                {{ $contact->unread_count }}
                            </span>
                        @endif
                    </button>
                @empty
                    <div class="flex items-center justify-center h-full py-12">
                        <p class="text-sm text-slate-400">No contacts found</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
