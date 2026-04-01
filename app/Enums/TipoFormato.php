<?php

namespace App\Enums;

enum TipoFormato: string
{
    case F01 = 'F01';
    case F02 = 'F02';
    case F03 = 'F03';
    case F04 = 'F04';
    case F05 = 'F05';
    case F06 = 'F06';
    case F07 = 'F07';
    case F08 = 'F08';
    case F09 = 'F09';
    case F10 = 'F10';
    case F11 = 'F11';
    case F12 = 'F12';
    case F13 = 'F13';

    public function label(): string
    {
        return match ($this) {
            self::F01 => 'F01 - Auditorías realizadas',
            self::F02 => 'F02 - Banco de datos inscritos',
            self::F03 => 'F03 - Prestadores con acceso a datos',
            self::F04 => 'F04 - Datos sensibles',
            self::F05 => 'F05 - Personal autorizado al BD',
            self::F06 => 'F06 - Acceso soporte no autorizado',
            self::F07 => 'F07 - Inventario de soportes',
            self::F08 => 'F08 - Ingreso y salida de soportes',
            self::F09 => 'F09 - Notificación de incidencias',
            self::F10 => 'F10 - Resolución de incidencias',
            self::F11 => 'F11 - Recuperación de datos',
            self::F12 => 'F12 - Copias de seguridad',
            self::F13 => 'F13 - Destrucción de activos',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::F01 => 'AUD',
            self::F02 => 'BD',
            self::F03 => 'PRES',
            self::F04 => 'DS',
            self::F05 => 'PA',
            self::F06 => 'AS',
            self::F07 => 'INV',
            self::F08 => 'IS',
            self::F09 => 'INC',
            self::F10 => 'RES',
            self::F11 => 'REC',
            self::F12 => 'BAK',
            self::F13 => 'DEST',
        };
    }

    public function psc(): string
    {
        return match ($this) {
            self::F01 => 'PSC000001',
            self::F02 => 'PSC000001',
            self::F03 => 'PSC000001 / PSC000002',
            self::F04 => 'PSC000001 / PSC000-46',
            self::F05 => 'PSC000001',
            self::F06 => 'PSC000001',
            self::F07 => 'PSC000003 / PSC000004',
            self::F08 => 'PSC000003',
            self::F09 => 'PSC000001 / PSC000-25',
            self::F10 => 'PSC000-25',
            self::F11 => 'PSC000001 / PSC000-25',
            self::F12 => 'PSC000003 / PSC000-15',
            self::F13 => 'PSC000003 / PSC000004',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::F01 => 'Lleva el control de las revisiones de seguridad que se hacen en la empresa. Anota quien la hizo, que encontro y si hay algo que corregir.',
            self::F02 => 'Lista todas las bases de datos con informacion personal que maneja la empresa. Sirve para saber que datos tenemos y quien puede verlos.',
            self::F03 => 'Registra a los proveedores o empresas externas que pueden ver datos personales. Ayuda a controlar quien de afuera tiene acceso.',
            self::F04 => 'Lleva el control de los datos mas delicados (como datos de salud o religion). Indica donde estan guardados y quien puede acceder.',
            self::F05 => 'Registra que personas tienen permiso para ver las bases de datos. Es como una lista de acceso autorizado.',
            self::F06 => 'Documenta cuando alguien de soporte tecnico accede sin autorizacion o de forma sospechosa. Sirve para investigar accesos indebidos.',
            self::F07 => 'Lista todos los dispositivos donde se guardan datos: discos, USB, servidores, archivos fisicos, etc. Es un inventario de donde esta la informacion.',
            self::F08 => 'Controla cuando entra o sale un dispositivo con datos de la empresa. Registra de donde viene, a donde va y quien lo autorizo.',
            self::F09 => 'Se usa cuando ocurre un problema de seguridad: alguien accedio sin permiso, se perdieron datos, hubo un virus, etc. Es el reporte inicial.',
            self::F10 => 'Documenta como se resolvio un problema de seguridad que ya fue reportado (F09). Explica que se hizo y como evitar que pase de nuevo.',
            self::F11 => 'Registra cuando se recuperan datos despues de un problema. Anota quien lo autorizo, quien lo hizo y como se realizo.',
            self::F12 => 'Lleva el control de las copias de respaldo (backups). Registra que se copio, cuando y con que frecuencia.',
            self::F13 => 'Registra cuando se destruye informacion de forma segura. Documenta que se destruyo, como y quien lo autorizo.',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
