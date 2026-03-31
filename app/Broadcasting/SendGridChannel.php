<?php

namespace App\Broadcasting;

use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use SendGrid;
use SendGrid\Mail\Mail;

class SendGridChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        // Call toMail() to reuse your existing MailMessage + Blade template
        $mailMessage = $notification->toMail($notifiable);

        // Render markdown messages through Laravel's markdown renderer
        // so <x-mail::...> components resolve correctly.
        $html = '';
        $text = '';

        if (!empty($mailMessage->markdown)) {
            $markdown = app(Markdown::class);
            $html = (string) $markdown->render($mailMessage->markdown, $mailMessage->viewData);

            $text = method_exists($markdown, 'renderText')
                ? (string) $markdown->renderText($mailMessage->markdown, $mailMessage->viewData)
                : trim(strip_tags($html));
        } elseif (!empty($mailMessage->view)) {
            $html = view($mailMessage->view, $mailMessage->viewData)->render();
            $text = trim(strip_tags($html));
        } else {
            $html = nl2br(e(implode("\n", $mailMessage->introLines ?? [])));
            $text = trim(implode("\n", $mailMessage->introLines ?? []));
        }

        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));
        $email->addTo($notifiable->email, $notifiable->first_name . ' ' . $notifiable->last_name);
        $email->setSubject($mailMessage->subject ?? ('Notification from ' . config('app.name')));
        $email->addContent('text/plain', $text);
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
        } catch (\Throwable $e) {
            Log::error('SendGrid exception: ' . $e->getMessage());
        }
    }
}
