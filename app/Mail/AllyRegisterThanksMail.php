<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmación al local que se registró desde /register: su solicitud ya está
 * en la bandeja de ventas.
 */
class AllyRegisterThanksMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Gracias por registrarte en Rumbero Extremo! Tu solicitud está en revisión',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ally-register-thanks');
    }
}
