<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use SendGrid;
use SendGrid\Mail\Mail;
use Illuminate\Support\Facades\Log;

class SendGridChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        // Call toMail() to reuse your existing MailMessage + Blade template
        $mailMessage = $notification->toMail($notifiable);

        // Render the Blade markdown view to HTML
        $html = view($mailMessage->markdown, $mailMessage->viewData)->render();

        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));
        $email->addTo($notifiable->email, $notifiable->first_name . ' ' . $notifiable->last_name);
        $email->setSubject($mailMessage->subject);
        $email->addContent('text/plain', strip_tags($html));
        $email->addContent('text/html', $html);

        try {
            $sg       = new SendGrid(config('services.sendgrid.api_key'));
            $response = $sg->send($email);

            if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
                Log::error('SendGrid failed', [
                    'status' => $response->statusCode(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SendGrid exception: ' . $e->getMessage());
        }
    }
}
