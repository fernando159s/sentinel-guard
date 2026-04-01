<?php

namespace App\Jobs;

use App\Mail\SecuriformMail;
use App\Models\NotificacionEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retries (backoff: 10s, 30s, 60s).
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public string $destinatario,
        public string $nombreDestino,
        public string $asunto,
        public string $saludo,
        public string $cuerpo,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?string $piePagina = null,
        public ?string $templateSlug = null,
        public array $templateVariables = [],
    ) {}

    /**
     * Dispatch a job using a database email template.
     * The template is resolved at send-time, not at dispatch-time.
     */
    public static function fromTemplate(
        string $destinatario,
        string $nombreDestino,
        string $templateSlug,
        array $templateVariables = [],
        string $saludo = '',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $piePagina = null,
    ): self {
        return new self(
            destinatario: $destinatario,
            nombreDestino: $nombreDestino,
            asunto: '',
            saludo: $saludo,
            cuerpo: '',
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            piePagina: $piePagina,
            templateSlug: $templateSlug,
            templateVariables: $templateVariables,
        );
    }

    public function handle(): void
    {
        $mailable = $this->buildMailable();

        $notificacion = NotificacionEmail::create([
            'destinatario' => $this->destinatario,
            'nombre_destino' => $this->nombreDestino,
            'asunto' => $mailable->asunto,
            'cuerpo_html' => $mailable->cuerpo,
            'estado' => 'pendiente',
            'intentos' => $this->attempts(),
            'fecha_programada' => now(),
            'created_at' => now(),
        ]);

        try {
            Mail::to($this->destinatario, $this->nombreDestino)->send($mailable);

            $notificacion->update([
                'estado' => 'enviado',
                'intentos' => $this->attempts(),
                'fecha_envio' => now(),
            ]);
        } catch (\Throwable $e) {
            $notificacion->update([
                'estado' => 'error',
                'intentos' => $this->attempts(),
                'error_mensaje' => mb_substr($e->getMessage(), 0, 500),
            ]);

            Log::error('SendEmailJob failed', [
                'to' => $this->destinatario,
                'subject' => $mailable->asunto,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure after all retries exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SendEmailJob permanently failed', [
            'to' => $this->destinatario,
            'subject' => $this->asunto,
            'error' => $exception?->getMessage(),
        ]);
    }

    private function buildMailable(): SecuriformMail
    {
        if ($this->templateSlug) {
            return SecuriformMail::fromTemplate(
                slug: $this->templateSlug,
                variables: $this->templateVariables,
                fallbackAsunto: $this->asunto,
                fallbackCuerpo: $this->cuerpo,
                saludo: $this->saludo,
                actionUrl: $this->actionUrl,
                actionLabel: $this->actionLabel,
                piePagina: $this->piePagina,
            );
        }

        return new SecuriformMail(
            asunto: $this->asunto,
            saludo: $this->saludo,
            cuerpo: $this->cuerpo,
            actionUrl: $this->actionUrl,
            actionLabel: $this->actionLabel,
            piePagina: $this->piePagina,
        );
    }
}
