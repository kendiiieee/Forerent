<x-mail::message>
    # Welcome to {{ config('app.name') }}!

    Hello {{ $recipientName }},

    We're excited to let you know that your account as a <strong>{{ $accountType }}</strong> has been created for <strong>{{ config('app.name') }}</strong>.

    Here are your login credentials:

    <strong>Email:</strong> {{ $email }}<br>
    <strong>Temporary Password:</strong> {{ $tempPassword }}

    <em>For your security, please change this temporary password after your first login.</em>

    <x-mail::button :url="$loginUrl">
        Login to {{ config('app.name') }}
    </x-mail::button>

    If you have any questions or need help, feel free to reply to this email or contact our support team.

    Thank you for joining us!<br>
    <strong>The {{ config('app.name') }} Team</strong>
</x-mail::message>
