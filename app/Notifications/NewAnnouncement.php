<?php

namespace App\Notifications;

use App\Broadcasting\SendGridChannel;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncement extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Announcement $announcement)
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
        $senderName = trim((string) ($this->announcement->author?->first_name.' '.$this->announcement->author?->last_name));

        return (new MailMessage)
            ->subject("New Announcement from ForeRent: {$this->announcement->headline}")
            ->markdown('mail.new-announcement', [
                'announcement' => $this->announcement,
                'user' => $notifiable,
                'senderName' => $senderName !== '' ? $senderName : 'ForeRent Team',
                'senderRole' => $this->announcement->sender_role,
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
