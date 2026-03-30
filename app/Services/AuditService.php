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
    ): AuditLog {
        return AuditLog::create([
            'usuario_id' => auth()->id(),
            'empresa_id' => auth()->user()?->empresa_id,
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
