<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base Mailable for SecuriForm.
 * Uses the corporate layout template with customizable subject, greeting, body and action.
 */
class SecuriformMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $asunto,
        public string $saludo,
        public string $cuerpo,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?string $piePagina = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.securiform',
            with: [
                'saludo' => $this->saludo,
                'cuerpo' => $this->cuerpo,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
                'piePagina' => $this->piePagina,
            ],
        );
    }
}
