<?php

namespace App\Enums;

enum ModalidadPago: string
{
    case Mensual = 'mensual';
    case Anual = 'anual';
    case PagoUnico = 'pago_unico';
    case Gratuito = 'gratuito';

    public function label(): string
    {
        return match ($this) {
            self::Mensual => 'Pago mensual',
            self::Anual => 'Pago anual',
            self::PagoUnico => 'Pago único',
            self::Gratuito => 'Gratuito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Mensual => 'warning',
            self::Anual => 'info',
            self::PagoUnico => 'success',
            self::Gratuito => 'gray',
        };
    }

    /** ¿La modalidad implica una renovación con fecha de vencimiento? */
    public function esRecurrente(): bool
    {
        return in_array($this, [self::Mensual, self::Anual], true);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $m) => [$m->value => $m->label()])
            ->all();
    }
}
