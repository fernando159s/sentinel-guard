<?php

namespace App\Enums;

enum ModalidadCapacitacion: string
{
    case Presencial = 'presencial';
    case Virtual = 'virtual';

    public function label(): string
    {
        return match ($this) {
            self::Presencial => 'Presencial',
            self::Virtual => 'Virtual',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Presencial => 'success',
            self::Virtual => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Presencial => 'heroicon-o-user-group',
            self::Virtual => 'heroicon-o-computer-desktop',
        };
    }
}
