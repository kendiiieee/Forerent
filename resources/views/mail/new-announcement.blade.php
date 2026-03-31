<x-mail::message>
    # {{ $announcement->headline }}

    Hello {{ $user->first_name }} {{ $user->last_name }},

    {!! nl2br(e($announcement->details)) !!}

    <x-mail::panel>
        **Sent by:** {{ $senderName }} ({{ ucfirst(str_replace('_', ' ', $senderRole)) }})
    </x-mail::panel>

    <x-mail::button :url="config('app.url')">
        Open Forerent
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
