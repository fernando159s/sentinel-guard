<?php

namespace App\Enums;

enum TipoActivoDigital: string
{
    case Whatsapp = 'whatsapp';
    case Meta = 'meta';
    case SuscripcionSaas = 'suscripcion_saas';
    case Dominio = 'dominio';
    case LicenciaUnica = 'licencia_unica';
    case Correo = 'correo';
    case RedesSociales = 'redes_sociales';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Whatsapp => 'Cuenta WhatsApp',
            self::Meta => 'Cuenta Meta / Business Manager',
            self::SuscripcionSaas => 'Suscripción SaaS',
            self::Dominio => 'Dominio web',
            self::LicenciaUnica => 'Licencia de pago único',
            self::Correo => 'Cuenta de correo',
            self::RedesSociales => 'Redes sociales',
            self::Otro => 'Otro',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Whatsapp => 'heroicon-o-chat-bubble-left-right',
            self::Meta => 'heroicon-o-building-storefront',
            self::SuscripcionSaas => 'heroicon-o-cloud',
            self::Dominio => 'heroicon-o-globe-alt',
            self::LicenciaUnica => 'heroicon-o-key',
            self::Correo => 'heroicon-o-envelope',
            self::RedesSociales => 'heroicon-o-hashtag',
            self::Otro => 'heroicon-o-square-3-stack-3d',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
