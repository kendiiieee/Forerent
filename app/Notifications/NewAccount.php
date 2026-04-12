<?php

namespace App\Notifications;

use App\Broadcasting\SendGridChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccount extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $email, public string $password, public string $role)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (config('mail.default') === 'smtp') {
            $smtpHost = strtolower((string) config('mail.mailers.smtp.host', ''));

            if (str_contains($smtpHost, 'gmail.com')) {
                return ['mail'];
            }
        }

        return [SendGridChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = $notifiable->first_name.' '.$notifiable->last_name;

        return (new MailMessage)
            ->subject('Your account credentials for ForeRent')
            ->markdown('mail.new-account', [
                'recipientName' => $recipientName,
                'accountType' => $this->role,
                'email' => $this->email,
                'tempPassword' => $this->password,
                'loginUrl' => route('login'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
