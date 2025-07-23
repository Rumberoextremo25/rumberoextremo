<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactData;

    /**
     * Create a new message instance.
     */
    public function __construct($contactData)
    {
        $this->contactData = $contactData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Hemos recibido tu mensaje en Rumbero Extremo!', // Asunto del correo
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact_confirmation', // Usa la vista Markdown para el contenido
            // También podrías usar 'view: 'emails.contact_confirmation', si prefieres Blade HTML
        );
    }

    // Si es necesario para inlinar CSS (Laravel 8 o si no se inlina automáticamente)
    public function build()
    {
        return $this->markdown('emails.contact-confirmation')
                    ->withCss(resource_path('css/emails.css')); // Ruta a tu archivo CSS
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
