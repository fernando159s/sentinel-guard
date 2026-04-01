<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base Mailable for SecuriForm.
 * Reads subject/body from email_templates table when a slug is provided.
 * Falls back to constructor values if template is not found or inactive.
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

    /**
     * Create a Mailable from a database template with variable replacement.
     * Falls back to provided defaults if the template is not found or inactive.
     */
    public static function fromTemplate(
        string $slug,
        array $variables = [],
        string $fallbackAsunto = '',
        string $fallbackCuerpo = '',
        string $saludo = '',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $piePagina = null,
    ): self {
        $template = EmailTemplate::findBySlug($slug);

        if ($template) {
            $asunto = $template->renderAsunto($variables);
            $cuerpo = $template->renderContenido($variables);
        } else {
            $asunto = $fallbackAsunto;
            $cuerpo = $fallbackCuerpo;
        }

        return new self(
            asunto: $asunto,
            saludo: $saludo,
            cuerpo: $cuerpo,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            piePagina: $piePagina,
        );
    }

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
