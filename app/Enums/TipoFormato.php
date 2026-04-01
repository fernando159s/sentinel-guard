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
            self::F01 => 'Documenta las auditorias internas y externas realizadas sobre los sistemas de informacion, registrando responsables, resultados y acciones correctivas.',
            self::F02 => 'Inventario de los bancos de datos personales inscritos ante la Autoridad, incluyendo su categoria, areas con acceso y descripcion.',
            self::F03 => 'Registro de prestadores de servicios (terceros) que tienen acceso a datos personales, con detalle de contrato y datos facilitados.',
            self::F04 => 'Inventario de datos sensibles que maneja la organizacion, su ubicacion y las areas autorizadas para acceder a ellos.',
            self::F05 => 'Control del personal autorizado para acceder a los bancos de datos, con fecha de asignacion y banco correspondiente.',
            self::F06 => 'Registro de accesos de soporte tecnico no autorizado o sospechoso, documentando quien accedio, cuando y desde donde.',
            self::F07 => 'Inventario de soportes fisicos y digitales (discos, USB, servidores, expedientes) que contienen datos personales.',
            self::F08 => 'Control de ingresos y salidas de soportes fisicos/digitales, con trazabilidad de origen, destino y autorizacion.',
            self::F09 => 'Notificacion y documentacion de incidencias de seguridad (accesos no autorizados, perdida de datos, malware, fallos de sistema).',
            self::F10 => 'Documentacion de la resolucion de incidencias previamente reportadas (F09), incluyendo medidas adoptadas y acciones preventivas.',
            self::F11 => 'Registro de procesos de recuperacion de datos tras una incidencia, con autorizacion, responsable y proceso ejecutado.',
            self::F12 => 'Control de las copias de seguridad (backups) realizadas, su contenido, periodicidad y fecha de ejecucion.',
            self::F13 => 'Registro de destruccion segura de activos de informacion, documentando metodo, responsable y autorizacion.',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
