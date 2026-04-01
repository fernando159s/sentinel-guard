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
    ) {}

    public function handle(): void
    {
        $notificacion = NotificacionEmail::create([
            'destinatario' => $this->destinatario,
            'nombre_destino' => $this->nombreDestino,
            'asunto' => $this->asunto,
            'cuerpo_html' => $this->cuerpo,
            'estado' => 'pendiente',
            'intentos' => $this->attempts(),
            'fecha_programada' => now(),
            'created_at' => now(),
        ]);

        try {
            $mailable = new SecuriformMail(
                asunto: $this->asunto,
                saludo: $this->saludo,
                cuerpo: $this->cuerpo,
                actionUrl: $this->actionUrl,
                actionLabel: $this->actionLabel,
                piePagina: $this->piePagina,
            );

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
                'subject' => $this->asunto,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw so Laravel retries
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
}
