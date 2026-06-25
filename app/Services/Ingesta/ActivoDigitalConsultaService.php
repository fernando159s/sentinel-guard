<?php

namespace App\Services\Ingesta;

use App\Enums\ModalidadPago;
use App\Models\ActivoDigital;
use App\Models\User;

/**
 * Núcleo de CONSULTA de Activos Digitales: busca activos respetando el tenant
 * del actor. Es agnóstico del canal — hoy lo usan el servidor MCP y el chat
 * in-app a través de ConsultarActivosDigitalesTool.
 *
 * El aislamiento por empresa lo aplica el EmpresaScope global (basado en el
 * usuario autenticado); para un super admin (sin empresa) se permite acotar
 * con un empresa_id opcional. NUNCA devuelve credenciales de acceso.
 */
class ActivoDigitalConsultaService
{
    /**
     * @param  array<string,mixed>  $filtros
     * @return array{total:int,activos:array<int,array<string,mixed>>}
     */
    public function buscar(array $filtros, User $actor): array
    {
        $query = ActivoDigital::query()
            ->with('empresa:id,razon_social')
            ->latest('id');

        // El EmpresaScope ya filtra por la empresa del usuario. Un super admin
        // (sin empresa propia) puede acotar opcionalmente a una empresa concreta.
        if (! $actor->empresa_id && ! empty($filtros['empresa_id'])) {
            $query->where('empresa_id', (int) $filtros['empresa_id']);
        }

        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['texto'])) {
            $texto = $filtros['texto'];
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', "%{$texto}%")
                    ->orWhere('proveedor', 'like', "%{$texto}%")
                    ->orWhere('codigo_interno', 'like', "%{$texto}%")
                    ->orWhere('identificador', 'like', "%{$texto}%");
            });
        }

        if (! empty($filtros['solo_por_vencer'])) {
            $dias = (int) ($filtros['dias_vencimiento'] ?? 30);
            $query->whereNotNull('fecha_vencimiento')
                ->whereIn('modalidad_pago', [ModalidadPago::Mensual->value, ModalidadPago::Anual->value])
                ->where('fecha_vencimiento', '<=', now()->addDays($dias));
        }

        $limite = min(max((int) ($filtros['limite'] ?? 20), 1), 50);

        $items = $query->limit($limite)->get();

        return [
            'total' => $items->count(),
            'activos' => $items->map(fn (ActivoDigital $a): array => [
                'codigo' => $a->codigo_interno,
                'nombre' => $a->nombre,
                'tipo' => $a->tipo?->label(),
                'estado' => $a->estado?->label(),
                'proveedor' => $a->proveedor,
                'identificador' => $a->identificador,
                'modalidad_pago' => $a->modalidad_pago?->label(),
                'costo' => $a->costo !== null ? trim($a->moneda.' '.$a->costo) : null,
                'vencimiento' => $a->fecha_vencimiento?->format('d/m/Y'),
                // La empresa solo es relevante para un super admin (ve varias).
                'empresa' => $actor->empresa_id ? null : $a->empresa?->razon_social,
            ])->all(),
        ];
    }
}
