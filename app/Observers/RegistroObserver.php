<?php

namespace App\Observers;

use App\Jobs\SendEmailJob;
use App\Models\Registro;
use App\Models\User;

class RegistroObserver
{
    public function created(Registro $registro): void
    {
        if ($registro->tipo_formato !== 'F09') {
            return;
        }

        $this->notifyAdminEmpresa($registro);
    }

    private function notifyAdminEmpresa(Registro $registro): void
    {
        if (! $registro->empresa_id) {
            return;
        }

        $admins = User::where('empresa_id', $registro->empresa_id)
            ->role('admin_empresa')
            ->where('estado', 'activo')
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $datos = $registro->datos ?? [];
        $tipo = $this->labelTipoIncidencia($datos['tipo_incidencia'] ?? 'otro');
        $severidad = ucfirst($datos['severidad'] ?? 'media');
        $registroUrl = url("admin/{$registro->empresa?->ruc}/registros/{$registro->id}/edit");

        foreach ($admins as $admin) {
            $job = SendEmailJob::fromTemplate(
                destinatario: $admin->email,
                nombreDestino: $admin->name,
                templateSlug: 'nueva_incidencia',
                templateVariables: [
                    'nombre' => $admin->name,
                    'numero_incidencia' => $registro->numero_registro,
                    'tipo' => $tipo,
                    'severidad' => $severidad,
                    'enlace_registro' => $registroUrl,
                ],
                actionUrl: $registroUrl,
                actionLabel: 'Ver incidencia',
            );

            dispatch($job);
        }
    }

    private function labelTipoIncidencia(string $tipo): string
    {
        return match ($tipo) {
            'acceso_no_autorizado' => 'Acceso no autorizado',
            'perdida_datos' => 'Perdida de datos',
            'fuga_informacion' => 'Fuga de informacion',
            'malware' => 'Malware',
            'fallo_sistema' => 'Fallo de sistema',
            default => 'Otro',
        };
    }
}
