<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SelamatDatangMail extends Mailable
{
    public function __construct(public readonly string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Selamat datang di Jatrilrun');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.selamat-datang');
    }
}
