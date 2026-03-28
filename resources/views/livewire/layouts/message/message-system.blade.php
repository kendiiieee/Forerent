<div
    class="bg-white rounded-[20px] shadow-sm flex h-full overflow-hidden border border-slate-200 font-sans relative"
    x-data="{}"
>

    {{-- ================================================= --}}
    {{-- PANE 1: CHAT LIST --}}
    {{-- ================================================= --}}
    <div class="w-[300px] xl:w-[340px] flex-shrink-0 flex flex-col border-r border-slate-100">

        <div class="p-5 pb-2">
            <h2 class="text-[#0C0B50] font-bold text-sm tracking-widest mb-4">CHATS</h2>

            {{-- Search Bar --}}
            <div class="relative mb-5">
                <input
                    type="text"
                    placeholder="Search..."
                    wire:model.live="search"
                    class="w-full bg-[#F4F6FB] border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-xs focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-400 text-slate-700 transition"
                >
                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Dynamic Tabs based on allowed roles --}}
            <div class="flex gap-5 border-b border-slate-100 mb-1">
                @foreach($this->allowedTabs as $tab)
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        class="pb-2.5 text-xs font-bold transition-all relative whitespace-nowrap
                            {{ $activeTab === $tab ? 'text-[#0C0B50]' : 'text-slate-400 hover:text-slate-600' }}"
                    >
                        {{ $this->tabLabels[$tab] ?? ucfirst($tab) }}
                        @if($activeTab === $tab)
                            <div class="absolute bottom-0 left-0 w-full h-[2.5px] bg-[#0C0B50] rounded-t-full"></div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Chat List --}}
        <div class="flex-1 overflow-y-auto px-3 space-y-0.5 pb-4"
             wire:poll.3000ms="$refresh"
             style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
            @forelse($chats as $chat)
                <button
                    wire:click="selectChat({{ $chat->user_id }})"
                    class="w-full flex items-center p-3 rounded-xl transition-all group
                        {{ $selectedUserId === $chat->user_id
                            ? 'bg-[#EEF3FF] border border-blue-100'
                            : 'hover:bg-slate-50 border border-transparent' }}"
                >
                    {{-- Avatar + Badge --}}
                    <div class="relative flex-shrink-0">
                        <img
                            src="{{ $chat->profile_image_url }}"
                            class="w-11 h-11 rounded-full object-cover border-2
                                {{ $selectedUserId === $chat->user_id ? 'border-blue-300' : 'border-slate-100' }}"
                        >
                        @if($chat->unread_count > 0)
                            <div class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-[#3B5BDB] text-white text-[9px] font-bold flex items-center justify-center rounded-full border-2 border-white px-1">
                                {{ $chat->unread_count }}
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="ml-3 text-left flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h4 class="text-xs font-bold truncate
                                {{ $selectedUserId === $chat->user_id ? 'text-[#0C0B50]' : 'text-slate-700' }}">
                                {{ $chat->first_name }} {{ $chat->last_name }}
                            </h4>
                            <span class="text-[9px] text-slate-400 ml-2 flex-shrink-0">{{ $chat->last_time }}</span>
                        </div>
                        <p class="text-[10px] truncate
                            {{ $chat->unread_count > 0 ? 'font-semibold text-slate-800' : 'text-slate-400' }}">
                            {{ $chat->last_message }}
                        </p>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                    <svg class="w-10 h-10 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-xs">No contacts found</p>
                </div>
            @endforelse
        </div>
    </div>


    {{-- ================================================= --}}
    {{-- PANE 2: CONVERSATION --}}
    {{-- ================================================= --}}
    <div class="flex-1 flex flex-col bg-[#FAFBFF] transition-all duration-300 min-w-0">

        @if($activeChatUser)
            {{-- Header --}}
            <div
                wire:click="toggleProfile"
                class="h-16 px-6 flex items-center border-b border-slate-100 bg-white shadow-sm z-10 cursor-pointer hover:bg-[#F8FAFF] transition-colors group"
            >
                <div class="relative">
                    <img
                        src="{{ $activeChatUser->profile_image_url }}"
                        class="w-9 h-9 rounded-full object-cover border border-blue-100"
                    >
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 rounded-full border-2 border-white"></div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <h3 class="text-[#0C0B50] font-bold text-sm truncate">
                        {{ $activeChatUser->first_name }} {{ $activeChatUser->last_name }}
                    </h3>
                    <p class="text-[10px] text-slate-400">{{ ucfirst($activeChatUser->role) }}</p>
                </div>
                {{-- Info icon --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors
                    {{ $showProfile ? 'bg-[#0C0B50] text-white' : 'text-slate-300 group-hover:bg-slate-100 group-hover:text-[#0C0B50]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Messages Area --}}
            <div
                id="messages-container"
                class="flex-1 overflow-y-auto p-6 space-y-1"
                style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;"
                wire:poll.2000ms="$refresh"
                x-init="$el.scrollTop = $el.scrollHeight"
                x-on:livewire:navigated.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            >
                @forelse($groupedMessages as $date => $messages)

                    {{-- Date Separator --}}
                    <div class="flex items-center gap-3 py-3">
                        <div class="flex-1 h-px bg-slate-100"></div>
                        <span class="text-[10px] text-slate-400 font-medium px-2">
                            {{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : (\Carbon\Carbon::parse($date)->isYesterday() ? 'Yesterday' : \Carbon\Carbon::parse($date)->format('F j, Y')) }}
                        </span>
                        <div class="flex-1 h-px bg-slate-100"></div>
                    </div>

                    @foreach($messages as $msg)
                        @php $isMe = $msg->sender_id === auth()->id(); @endphp
                        <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} mb-1">

                            {{-- Their Avatar --}}
                            @if(!$isMe)
                                <img
                                    src="{{ $activeChatUser->profile_image_url }}"
                                    class="w-7 h-7 rounded-full mr-2 self-end mb-4 flex-shrink-0"
                                >
                            @endif

                            <div class="flex flex-col max-w-[60%] {{ $isMe ? 'items-end' : 'items-start' }}">
                                <div class="px-4 py-2.5 text-sm rounded-2xl shadow-sm
                                    {{ $isMe
                                        ? 'bg-[#C8D9FD] text-[#0C0B50] rounded-br-sm'
                                        : 'bg-[#0C0A84] text-white rounded-bl-sm'
                                    }}">

                                    @if($msg->type === 'file')
                                        @if($msg->file_type === 'image')
                                            <a href="{{ Storage::url($msg->file_path) }}" target="_blank">
                                                <img src="{{ Storage::url($msg->file_path) }}"
                                                     class="max-w-[200px] rounded-xl border border-white/20 cursor-pointer hover:opacity-90 transition-opacity">
                                            </a>
                                        @else
                                            <a href="{{ Storage::url($msg->file_path) }}" target="_blank"
                                               class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-medium truncate max-w-[140px]">{{ $msg->message }}</p>
                                                    <p class="text-[10px] opacity-60">Document</p>
                                                </div>
                                            </a>
                                        @endif
                                    @else
                                        <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-300 mt-1 px-1">
                                    {{ $msg->created_at->format('g:i A') }}
                                    @if($isMe)
                                        @if($msg->is_read)
                                            <span class="text-blue-400">✓✓</span>
                                        @else
                                            <span class="text-slate-300">✓</span>
                                        @endif
                                    @endif
                                </span>
                            </div>

                            {{-- My Avatar --}}
                            @if($isMe)
                                @php $me = auth()->user(); @endphp
                                <img
                                    src="{{ $me->profile_image_url }}"
                                    class="w-7 h-7 rounded-full ml-2 self-end mb-4 flex-shrink-0"
                                >
                            @endif
                        </div>
                    @endforeach
                @empty
                    <div class="flex flex-col h-full items-center justify-center text-slate-400 pt-20">
                        <div class="w-16 h-16 rounded-full bg-[#EEF3FF] flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">No messages yet</p>
                        <p class="text-xs text-slate-400 mt-1">Say hello to {{ $activeChatUser->first_name }}!</p>
                    </div>
                @endforelse
            </div>

            {{-- Input Area --}}
            <div class="p-4 bg-white border-t border-slate-100">

                {{-- ================================================= --}}
                {{-- MESSAGE INPUT with upload state management         --}}
                {{-- uploading: true while $wire.upload() is in flight  --}}
                {{-- uploadError: set if the server rejects the file    --}}
                {{-- progress: 0-100 from Livewire upload progress evt  --}}
                {{-- ================================================= --}}
                <div
                    x-data="{
                        message: '',
                        uploading: false,
                        progress: 0,
                        uploadError: '',
                        resize() {
                            this.$refs.msgarea.style.height = 'auto';
                            this.$refs.msgarea.style.height = Math.min(this.$refs.msgarea.scrollHeight, 160) + 'px';
                        },
                        send() {
                            if (this.uploading) return;
                            if (this.message.trim() === '' && !$wire.attachment) return;
                            $wire.set('messageInput', this.message);
                            $wire.sendMessage();
                            this.message = '';
                            this.$nextTick(() => {
                                this.$refs.msgarea.style.height = 'auto';
                            });
                        },
                        handleFileChange(event) {
                            const file = event.target.files[0];
                            if (!file) return;

                            this.uploadError = '';
                            this.uploading = true;
                            this.progress = 0;

                            $wire.upload(
                                'attachment',
                                file,
                                () => {
                                    // Success — Livewire re-renders and shows the preview
                                    this.uploading = false;
                                    this.progress = 0;
                                },
                                () => {
                                    // Error — likely 422 validation rejection
                                    this.uploading = false;
                                    this.progress = 0;
                                    this.uploadError = 'File rejected. Max 10MB. Images and documents only.';
                                },
                                (progressEvent) => {
                                    this.progress = progressEvent.detail.progress;
                                }
                            );

                            event.target.value = '';
                        }
                    }"
                    class="flex flex-col bg-[#F4F6FB] border border-slate-200 rounded-2xl px-2 py-2 focus-within:ring-2 focus-within:ring-blue-200 focus-within:border-blue-300 transition-all"
                >

                    {{-- Upload Error Banner --}}
                    <div
                        x-show="uploadError !== ''"
                        x-transition
                        class="flex items-center gap-2 text-[11px] text-red-600 bg-red-50 border border-red-100 rounded-xl px-3 py-2 mb-2"
                    >
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="uploadError"></span>
                        <button type="button" @click="uploadError = ''" class="ml-auto text-red-400 hover:text-red-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Attachment Preview (rendered by Livewire after upload completes) --}}
                    @if($attachment)
                        <div class="flex items-center gap-2 mb-2 px-1">
                            <div class="relative flex items-center gap-3 bg-[#EEF3FF] rounded-xl p-2 pr-4 border border-blue-100 w-full">
                                @if(Str::startsWith($attachment->getMimeType(), 'image/'))
                                    <img src="{{ $attachment->temporaryUrl() }}" class="w-10 h-10 object-cover rounded-lg border border-blue-200 flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-[#3B5BDB] border border-blue-100 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex flex-col min-w-0 flex-1">
                                    <span class="text-xs font-bold text-[#0C0B50] truncate">{{ $attachment->getClientOriginalName() }}</span>
                                    <span class="text-[10px] text-slate-400">Ready to send</span>
                                </div>
                                <button
                                    type="button"
                                    wire:click="$set('attachment', null)"
                                    class="absolute -top-2 -right-2 bg-white text-slate-400 hover:text-red-500 rounded-full p-0.5 shadow border border-slate-200 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Upload Progress Bar (shown while uploading) --}}
                    <div x-show="uploading" x-transition class="px-1 mb-2">
                        <div class="flex items-center gap-3 bg-[#EEF3FF] rounded-xl p-2 pr-4 border border-blue-100">
                            {{-- Animated file icon --}}
                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-[#3B5BDB] border border-blue-100 flex-shrink-0">
                                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[11px] font-semibold text-[#0C0B50]">Uploading...</span>
                                    <span class="text-[10px] text-[#3B5BDB] font-bold" x-text="progress + '%'"></span>
                                </div>
                                {{-- Progress track --}}
                                <div class="w-full bg-blue-100 rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="bg-[#3B5BDB] h-1.5 rounded-full transition-all duration-200"
                                        :style="'width: ' + progress + '%'"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Text Input Row --}}
                    <div class="flex items-end">
                        <textarea
                            x-ref="msgarea"
                            x-model="message"
                            placeholder="Type a message here..."
                            rows="1"
                            @input="resize()"
                            wire:focus="markAsRead"
                            @keydown.enter.prevent="if (!$event.shiftKey) { send(); } else { message += '\n'; $nextTick(() => resize()); }"
                            :disabled="uploading"
                            class="flex-1 border-none focus:ring-0 text-sm px-3 placeholder-slate-400 text-slate-700 bg-transparent resize-none leading-relaxed py-1 disabled:opacity-50"
                            style="min-height: 36px; max-height: 160px; overflow-y: auto;"
                        ></textarea>

                        <div class="flex items-center gap-1 flex-shrink-0">

                            {{-- Hidden File Input --}}
                            <input
                                type="file"
                                x-ref="fileInput"
                                class="hidden"
                                x-on:change="handleFileChange($event)"
                            >

                            {{-- Attachment Button — shows spinner while uploading --}}
                            <button
                                type="button"
                                x-on:click="if (!uploading) $refs.fileInput.click()"
                                :class="uploading ? 'opacity-50 cursor-not-allowed' : 'hover:text-[#3B5BDB] hover:bg-blue-50'"
                                class="p-2 text-slate-400 transition-colors rounded-full"
                            >
                                {{-- Paperclip icon (default) --}}
                                <svg x-show="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{-- Spinner (while uploading) --}}
                                <svg x-show="uploading" class="w-5 h-5 animate-spin text-[#3B5BDB]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                            </button>

                            {{-- Send Button — disabled while uploading --}}
                            <button
                                type="button"
                                @click="send()"
                                :disabled="uploading"
                                :class="uploading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#1a1880] active:scale-95'"
                                class="p-2 bg-[#0C0B50] text-white rounded-xl transition-all shadow-sm"
                            >
                                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 bg-[#FAFBFF]">
                <div class="w-20 h-20 rounded-full bg-[#EEF3FF] flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="font-semibold text-slate-600">Select a conversation</p>
                <p class="text-xs mt-1">Choose from your contacts to start messaging</p>
            </div>
        @endif
    </div>


    {{-- ================================================= --}}
    {{-- PANE 3: PROFILE / ACCOUNT INFO --}}
    {{-- ================================================= --}}
    @if($showProfile && $activeChatUser)
        <div class="w-[280px] flex-shrink-0 bg-white border-l border-slate-100 flex flex-col overflow-y-auto"
             style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">

            {{-- Header --}}
            <div class="flex justify-between items-center px-5 pt-5 pb-4 border-b border-slate-50">
                <h3 class="text-[#0C0B50] font-bold text-sm tracking-wide">Account Information</h3>
                <button wire:click="toggleProfile" class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Profile Summary --}}
            <div class="flex flex-col items-center pt-6 pb-5 px-5 border-b border-slate-50">
                <div class="relative mb-3">
                    <div class="p-1 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100">
                        <img
                            src="{{ $activeChatUser->profile_image_url }}"
                            class="w-20 h-20 rounded-full object-cover"
                        >
                    </div>
                    <div class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-green-400 rounded-full border-2 border-white"></div>
                </div>
                <h2 class="text-[#0C0B50] font-bold text-base">{{ $activeChatUser->first_name }} {{ $activeChatUser->last_name }}</h2>
                <span class="mt-1 text-[10px] font-semibold bg-[#EEF3FF] text-[#3B5BDB] px-3 py-0.5 rounded-full">
                    {{ ucfirst($activeChatUser->role) }}
                </span>
            </div>

            {{-- Contact Details --}}
            <div class="px-5 py-4 border-b border-slate-50 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-[#EEF3FF] flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase tracking-wide font-medium">Email</p>
                        <p class="text-xs font-semibold text-slate-700 break-all">{{ $activeChatUser->email }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-[#EEF3FF] flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase tracking-wide font-medium">Contact</p>
                        <p class="text-xs font-semibold text-slate-700">{{ $activeChatUser->contact ?? 'N/A' }}</p>
                    </div>
                </div>

                {{-- Tenant-specific info --}}
                @if($activeChatUser->role === 'tenant' && isset($activeChatUser->unit))
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-[#EEF3FF] flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[9px] text-slate-400 uppercase tracking-wide font-medium">Unit</p>
                            <p class="text-xs font-semibold text-slate-700">{{ $activeChatUser->unit }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Media Section --}}
            <div class="px-5 pt-4 flex-1">
                <h4 class="text-xs font-bold text-[#0C0B50] mb-3">Media</h4>

                {{-- Media Tabs --}}
                <div class="flex gap-1 bg-[#F4F6FB] rounded-lg p-1 mb-4">
                    <button
                        wire:click="setMediaTab('images')"
                        class="flex-1 text-[10px] font-semibold py-1.5 rounded-md transition-all
                            {{ $mediaTab === 'images' ? 'bg-white text-[#0C0B50] shadow-sm' : 'text-slate-400 hover:text-slate-600' }}"
                    >
                        Images
                        @if($mediaImages->count() > 0)
                            <span class="ml-1 text-[9px] text-blue-500">({{ $mediaImages->count() }})</span>
                        @endif
                    </button>
                    <button
                        wire:click="setMediaTab('documents')"
                        class="flex-1 text-[10px] font-semibold py-1.5 rounded-md transition-all
                            {{ $mediaTab === 'documents' ? 'bg-white text-[#0C0B50] shadow-sm' : 'text-slate-400 hover:text-slate-600' }}"
                    >
                        Documents
                        @if($mediaDocuments->count() > 0)
                            <span class="ml-1 text-[9px] text-blue-500">({{ $mediaDocuments->count() }})</span>
                        @endif
                    </button>
                </div>

                {{-- Images Grid --}}
                @if($mediaTab === 'images')
                    @if($mediaImages->count() > 0)
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach($mediaImages as $img)
                                <a href="{{ Storage::url($img->file_path) }}" target="_blank"
                                   class="aspect-square rounded-lg overflow-hidden border border-slate-100 hover:opacity-90 transition-opacity group">
                                    <img
                                        src="{{ Storage::url($img->file_path) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                        alt="{{ $img->message }}"
                                    >
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-slate-300">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-xs text-slate-400">No images shared yet</p>
                        </div>
                    @endif
                @endif

                {{-- Documents List --}}
                @if($mediaTab === 'documents')
                    @if($mediaDocuments->count() > 0)
                        <div class="space-y-2">
                            @foreach($mediaDocuments as $doc)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                   class="flex items-center gap-3 p-2.5 bg-[#F4F6FB] rounded-xl hover:bg-[#EEF3FF] transition-colors group border border-transparent hover:border-blue-100">
                                    <div class="w-8 h-8 rounded-lg bg-[#EEF3FF] flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100">
                                        <svg class="w-4 h-4 text-[#3B5BDB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-slate-700 truncate">{{ $doc->message }}</p>
                                        <p class="text-[9px] text-slate-400">{{ $doc->created_at->format('M j, Y') }}</p>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-slate-300">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-xs text-slate-400">No documents shared yet</p>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Bottom padding --}}
            <div class="h-4"></div>
        </div>
    @endif

</div>

{{-- Auto-scroll to bottom on new messages --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('scroll-to-bottom', () => {
            const el = document.getElementById('messages-container');
            if (el) el.scrollTop = el.scrollHeight;
        });

        // Auto-scroll on every poll refresh
        Livewire.hook('commit', ({ component, succeed }) => {
            succeed(() => {
                const el = document.getElementById('messages-container');
                if (el) {
                    const isNearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 100;
                    if (isNearBottom) el.scrollTop = el.scrollHeight;
                }
            });
        });
    });
</script>
