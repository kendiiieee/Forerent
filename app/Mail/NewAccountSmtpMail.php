<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAccountSmtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $password,
        public string $role,
        public string $firstName,
        public string $lastName
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your account credentials for ForeRent')
            ->markdown('mail.new-account', [
                'recipientName' => trim($this->firstName.' '.$this->lastName),
                'accountType' => $this->role,
                'email' => $this->email,
                'tempPassword' => $this->password,
                'loginUrl' => route('login'),
            ]);
    }
}
