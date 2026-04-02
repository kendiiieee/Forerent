<x-mail::message>
    # Community Update: {{ $announcement->headline }}

    Hello **{{ $user->first_name }} {{ $user->last_name }}**,

    We are writing to share an important update regarding your property. Please take a moment to read the details below:

    <x-mail::panel>
        {!! nl2br(e($announcement->details)) !!}
    </x-mail::panel>

    ### **Notice Information**
    <x-mail::table>
        | | |
        | :--- | :--- |
        | **Sent By:** | {{ $senderName }} |
        | **Position:** | {{ ucfirst(str_replace('_', ' ', $senderRole)) }} |
    </x-mail::table>

    If you would like to see more information or past updates, you can click the button below to visit our website:

    <x-mail::button :url="'https://forerent.onrender.com/'">
        Read Full Details Online
    </x-mail::button>

    If you have any questions about this notice, please reach out to your property manager directly.

    Best regards,
    **The ForeRent Team**
</x-mail::message>
