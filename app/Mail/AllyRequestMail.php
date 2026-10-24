<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a ventas cuando un local (aliado comercial) envía su solicitud desde la
 * web. El destinatario se configura desde el panel (Ajustes → Correo de ventas).
 */
class AllyRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
        public string $kind = 'aliado',
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->kind === 'afiliado'
            ? 'Nueva solicitud de afiliado — ' . ($this->data['full_name'] ?? 'Rumbero')
            : 'Nueva solicitud de local — ' . ($this->data['company_name'] ?? 'Aliado');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ally-request');
    }
}
