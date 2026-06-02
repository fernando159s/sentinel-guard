<?php

namespace App\Enums;

enum EstadoActivoDigital: string
{
    case Activo = 'activo';
    case Suspendido = 'suspendido';
    case Vencido = 'vencido';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Suspendido => 'Suspendido',
            self::Vencido => 'Vencido',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Suspendido => 'warning',
            self::Vencido => 'danger',
            self::Cancelado => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $e) => [$e->value => $e->label()])
            ->all();
    }
}
