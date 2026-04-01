<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Vista previa')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Vista previa del email')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalContent(function () {
                    $record = $this->record;
                    $sampleVars = $this->getSampleVariables($record->slug);
                    $asunto = $record->renderAsunto($sampleVars);
                    $cuerpo = $record->renderContenido($sampleVars);

                    $htmlPreview = view('emails.securiform', [
                        'asunto' => $asunto,
                        'saludo' => 'Hola ' . ($sampleVars['nombre'] ?? 'Usuario') . ',',
                        'cuerpo' => $cuerpo,
                        'actionUrl' => '#',
                        'actionLabel' => 'Ver en SecuriForm',
                        'piePagina' => 'Vista previa con datos de ejemplo',
                    ])->render();

                    return view('filament.pages.email-preview', [
                        'asunto' => $asunto,
                        'para' => $sampleVars['email'] ?? ($sampleVars['nombre'] ?? 'usuario') . '@empresa.com',
                        'htmlPreview' => $htmlPreview,
                    ]);
                }),

            Action::make('restore_default')
                ->label('Restaurar por defecto')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('¿Restaurar plantilla por defecto?')
                ->modalDescription('Se reemplazará el asunto y contenido actual con la plantilla original. Esta acción no se puede deshacer.')
                ->action(function () {
                    $this->record->restaurarDefault();
                    $this->fillForm();

                    Notification::make()
                        ->title('Plantilla restaurada')
                        ->body('Se restauró el contenido por defecto correctamente.')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function getSampleVariables(string $slug): array
    {
        return match ($slug) {
            'bienvenida' => [
                'nombre' => 'Juan Pérez',
                'empresa' => 'Estudio Palacios Abogados S.A.C.',
                'email' => 'juan.perez@empresa.com',
                'enlace_login' => '#',
            ],
            'reset_password' => [
                'nombre' => 'Juan Pérez',
                'enlace_reset' => '#',
                'minutos_expiracion' => '60',
            ],
            'ticket_creado' => [
                'nombre' => 'Juan Pérez',
                'numero_ticket' => 'TK-2026-001',
                'asunto' => 'No puedo acceder al sistema',
                'enlace_ticket' => '#',
            ],
            'respuesta_ticket' => [
                'nombre' => 'Juan Pérez',
                'numero_ticket' => 'TK-2026-001',
                'asunto' => 'No puedo acceder al sistema',
                'quien_responde' => 'Agente de Soporte',
                'enlace_ticket' => '#',
            ],
            'ticket_resuelto' => [
                'nombre' => 'Juan Pérez',
                'numero_ticket' => 'TK-2026-001',
                'asunto' => 'No puedo acceder al sistema',
                'enlace_ticket' => '#',
            ],
            'nueva_incidencia' => [
                'nombre' => 'Admin Empresa',
                'numero_incidencia' => 'INC-2026-004',
                'tipo' => 'Acceso no autorizado',
                'severidad' => 'Alta',
                'enlace_registro' => '#',
            ],
            default => [],
        };
    }
}
