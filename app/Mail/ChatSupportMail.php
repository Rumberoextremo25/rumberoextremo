<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al buzón de soporte cuando un usuario escribe en el chat y no hay IA
 * (o pide hablar con una persona).
 */
class ChatSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $sessionId,
        public string $body,
        public string $userName,
        public ?string $userEmail = null,
        public ?string $chatUrl = null,
        public ?string $whatsappUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo mensaje en el chat — ' . $this->userName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chat-support',
        );
    }
}
