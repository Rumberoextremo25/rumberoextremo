<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $confirmationToken;
    public $subscriberEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $confirmationToken, string $subscriberEmail)
    {
        $this->confirmationToken = $confirmationToken;
        $this->subscriberEmail = $subscriberEmail;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('¡Confirma tu suscripción al Newsletter de Rumbero Extremo!')
                    ->markdown('emails.newsletter_confirmation')
                    ->withCss(resource_path('css/emails.css'));
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}