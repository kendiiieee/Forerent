<?php

namespace App\Livewire\Concerns;

use App\Broadcasting\SendGridChannel;
use App\Mail\NewAccountSmtpMail;
use App\Models\User;
use App\Notifications\NewAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

trait SendsTenantWelcomeEmail
{
    private function attemptWelcomeEmailDelivery(User $createdUser, string $password): void
    {
        $notification = new NewAccount($createdUser->email, $password, $createdUser->role);

        $this->logWelcomeEmailPreview($notification, $createdUser);

        $sendGridAttempt = [
            'ok' => false,
            'status' => null,
            'error' => 'No SendGrid attempt data.',
        ];

        try {
            SendGridChannel::resetLastAttempt();
            Notification::send($createdUser, $notification);

            $sendGridAttempt = SendGridChannel::lastAttempt() ?? $sendGridAttempt;
        } catch (\Throwable $exception) {
            $sendGridAttempt = [
                'ok' => false,
                'status' => null,
                'error' => $exception->getMessage(),
            ];

            Log::warning('SendGrid call failed for tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);
        }

        if (($sendGridAttempt['ok'] ?? false) === true) {
            Log::info('Tenant welcome email sent via SendGrid.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'status' => $sendGridAttempt['status'] ?? null,
            ]);

            return;
        }

        Log::warning('Tenant welcome email SendGrid failed; trying SMTPS fallback.', [
            'tenant_id' => $createdUser->user_id,
            'tenant_email' => $createdUser->email,
            'sendgrid_status' => $sendGridAttempt['status'] ?? null,
            'sendgrid_error' => $sendGridAttempt['error'] ?? null,
        ]);

        try {
            Mail::mailer('smtp')
                ->to($createdUser->email)
                ->send(new NewAccountSmtpMail(
                    email: $createdUser->email,
                    password: $password,
                    role: $createdUser->role,
                    firstName: (string) ($createdUser->first_name ?? ''),
                    lastName: (string) ($createdUser->last_name ?? ''),
                ));

            Log::info('Tenant welcome email sent via SMTPS fallback.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'mailer' => 'smtp',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('SMTPS fallback failed for tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);

            if (method_exists($this, 'notifyWarning')) {
                $this->notifyWarning(
                    'Tenant approved, email retry failed',
                    'Email delivery failed due to provider limits. Tenant record was saved successfully.'
                );
            }
        }
    }

    private function logWelcomeEmailPreview(NewAccount $notification, User $createdUser): void
    {
        if (!config('services.sendgrid.preview_logging', false)) {
            return;
        }

        try {
            $mailMessage = $notification->toMail($createdUser);

            if (empty($mailMessage->subject)) {
                Log::warning('Tenant welcome email missing subject.', [
                    'tenant_id' => $createdUser->user_id,
                    'tenant_email' => $createdUser->email,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Error rendering tenant welcome email.', [
                'tenant_id' => $createdUser->user_id,
                'tenant_email' => $createdUser->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
