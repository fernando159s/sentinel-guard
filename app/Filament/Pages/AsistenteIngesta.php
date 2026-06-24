<?php

namespace App\Filament\Pages;

use App\Services\Asistente\AsistenteSecuriForm;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

/**
 * Chat in-app de SecuriForm. Es un componente Livewire (toda página Filament lo
 * es) que conversa con el asistente, el cual reusa las herramientas del MCP
 * para registrar y consultar activos digitales. Corre como el usuario logueado,
 * así que respeta su empresa (tenant) y permisos.
 */
class AsistenteIngesta extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Activos';

    protected static ?string $navigationLabel = 'Asistente';

    protected static ?string $title = 'Asistente SecuriForm';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.asistente-ingesta';

    /**
     * Historial visible de la conversación.
     *
     * @var array<int, array{role:string, content:string, herramientas?:array<int,string>}>
     */
    public array $mensajes = [];

    public string $entrada = '';

    public bool $procesando = false;

    /** Solo roles de gestión: las herramientas de ingesta exigen ese rol. */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function mount(): void
    {
        $this->mensajes = [[
            'role' => 'assistant',
            'content' => '¡Hola! Soy el asistente de SecuriForm. Puedo registrar y consultar activos digitales '
                .'(cuentas WhatsApp/Meta, suscripciones, dominios, licencias…) y resolver dudas sobre el sistema. '
                .'Solo hablo del contexto de SecuriForm y nunca pido credenciales. ¿En qué te ayudo?',
        ]];
    }

    /** Paso 1: registra el mensaje del usuario y lanza el procesamiento. */
    public function enviar(): void
    {
        $texto = trim($this->entrada);

        if ($texto === '' || $this->procesando) {
            return;
        }

        $this->mensajes[] = ['role' => 'user', 'content' => $texto];
        $this->entrada = '';
        $this->procesando = true;

        // Dispara el segundo request: el mensaje del usuario y el spinner ya
        // se pintan de inmediato mientras el modelo responde.
        $this->dispatch('asistente-responder');
    }

    /** Paso 2: consulta al asistente y agrega su respuesta. */
    public function responder(): void
    {
        if (! $this->procesando) {
            return;
        }

        try {
            $resultado = app(AsistenteSecuriForm::class)->responder($this->mensajes, auth()->user());

            $this->mensajes[] = [
                'role' => 'assistant',
                'content' => $resultado['texto'],
                'herramientas' => $resultado['herramientas'],
            ];
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('No se pudo obtener respuesta')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->mensajes[] = [
                'role' => 'assistant',
                'content' => 'Lo siento, hubo un problema al procesar tu mensaje. Inténtalo de nuevo en un momento.',
            ];
        } finally {
            $this->procesando = false;
        }
    }

    public function limpiar(): void
    {
        $this->mount();
    }
}
