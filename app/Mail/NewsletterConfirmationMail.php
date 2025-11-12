<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriberEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subscriberEmail)
    {
        $this->subscriberEmail = $subscriberEmail;
    }

    /**
     * Get the message content and subject definition.
     * Este método antiguo (build) maneja todo.
     */
    public function build()
    {
        return $this->subject('¡Gracias por suscribirte al Newsletter de Rumbero Extremo!')
                    // 1. Usamos 'markdown' para compilar el código @component
                    // 2. Apuntamos a la vista que contiene tu código de Newsletter
                    ->markdown('emails.newsletter_confirmation') 
                    // 3. Agregamos el CSS (opcional, si el CSS es necesario)
                    ->withCss(resource_path('css/emails.css'));
    }

    /**
     * Get the attachments for the message.
     * (Mantenemos attachments para compatibilidad)
     */
    public function attachments(): array
    {
        return [];
    }
}

// IMPORTANTE: Asegúrate de eliminar o comentar los métodos 
// public function envelope(): Envelope
// public function content(): Content