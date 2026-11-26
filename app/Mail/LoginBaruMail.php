<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginBaruMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly string $name,
        public readonly string $loginTime,
        public readonly string $ipAddress,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Login baru di Jatrilrun');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.login-baru');
    }
}
