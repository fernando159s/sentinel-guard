<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?int $empresaId = null,
    ): AuditLog {
        return AuditLog::create([
            'usuario_id' => auth()->id(),
            // Permite atribuir el log a la empresa de la entidad afectada (no la del actor):
            // util cuando un super_admin opera sobre datos de una empresa distinta a la suya.
            'empresa_id' => $empresaId ?? auth()->user()?->empresa_id,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
