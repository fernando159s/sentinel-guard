<?php

namespace App\Services;

use App\Enums\TipoFormato;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;

class FormatoFieldsService
{
    public static function getFields(TipoFormato $tipo): array
    {
        return match ($tipo) {
            TipoFormato::F01 => self::f01(),
            TipoFormato::F02 => self::f02(),
            TipoFormato::F03 => self::f03(),
            TipoFormato::F04 => self::f04(),
            TipoFormato::F05 => self::f05(),
            TipoFormato::F06 => self::f06(),
            TipoFormato::F07 => self::f07(),
            TipoFormato::F08 => self::f08(),
            TipoFormato::F09 => self::f09(),
            TipoFormato::F10 => self::f10(),
            TipoFormato::F11 => self::f11(),
            TipoFormato::F12 => self::f12(),
            TipoFormato::F13 => self::f13(),
        };
    }

    private static function f01(): array
    {
        return [
            Select::make('datos.tipo')->label('Tipo de auditoría')
                ->options(['interna' => 'Interna', 'externa' => 'Externa'])->required(),
            DatePicker::make('datos.fecha')->label('Fecha')->required(),
            TextInput::make('datos.responsable')->label('Responsable / Proveedor')->required(),
            Select::make('datos.resultado')->label('Resultado')
                ->options(['conforme' => 'Conforme', 'no_conforme' => 'No conforme', 'con_observaciones' => 'Con observaciones'])->required(),
            Select::make('datos.acciones_correctivas')->label('Acciones correctivas')
                ->options(['si' => 'Sí', 'no' => 'No'])->required(),
            Textarea::make('datos.observaciones')->label('Observaciones')->rows(3),
        ];
    }

    private static function f02(): array
    {
        return [
            TextInput::make('datos.nombre_bd')->label('Nombre del banco de datos')->required(),
            TextInput::make('datos.codigo_registro')->label('Código / Registro'),
            Textarea::make('datos.descripcion')->label('Descripción')->rows(3)->required(),
            TagsInput::make('datos.areas_acceso')->label('Unidades / Áreas con acceso'),
            Select::make('datos.categoria')->label('Categoría / Nivel')
                ->options(['publica' => 'Pública', 'interna' => 'Interna', 'confidencial' => 'Confidencial', 'sensible' => 'Sensible'])->required(),
        ];
    }

    private static function f03(): array
    {
        return [
            TextInput::make('datos.prestador')->label('Prestador del servicio')->required(),
            Textarea::make('datos.finalidad')->label('Finalidad')->rows(2)->required(),
            TagsInput::make('datos.datos_facilitados')->label('Datos facilitados (tipo)'),
            DatePicker::make('datos.fecha_contrato')->label('Fecha de contrato'),
            DatePicker::make('datos.vigencia')->label('Vigencia'),
            Textarea::make('datos.observaciones')->label('Observaciones')->rows(3),
        ];
    }

    private static function f04(): array
    {
        return [
            TextInput::make('datos.nombre')->label('Nombre')->required(),
            Textarea::make('datos.descripcion')->label('Descripción')->rows(3)->required(),
            Select::make('datos.ubicacion_tipo')->label('Ubicación tipo')
                ->options(['fisica' => 'Física', 'digital' => 'Digital', 'mixta' => 'Mixta'])->required(),
            TextInput::make('datos.ubicacion_detalle')->label('Ubicación detalle'),
            TagsInput::make('datos.areas_acceso')->label('Usuarios / Áreas con acceso'),
            Select::make('datos.categoria')->label('Categoría / Nivel')
                ->options(['publica' => 'Pública', 'interna' => 'Interna', 'confidencial' => 'Confidencial', 'sensible' => 'Sensible'])->required(),
        ];
    }

    private static function f05(): array
    {
        return [
            TextInput::make('datos.usuario')->label('Usuario')->required(),
            DateTimePicker::make('datos.fecha_asignacion')->label('Fecha y hora de asignación')->required(),
            TextInput::make('datos.banco_datos')->label('Banco de datos')->required(),
        ];
    }

    private static function f06(): array
    {
        return [
            Textarea::make('datos.descripcion_soporte')->label('Descripción del soporte')->rows(3)->required(),
            TextInput::make('datos.persona_accede')->label('Persona que accede')->required(),
            DatePicker::make('datos.fecha_acceso')->label('Fecha de acceso')->required(),
            TimePicker::make('datos.hora_acceso')->label('Hora de acceso')->required(),
        ];
    }

    private static function f07(): array
    {
        return [
            Select::make('datos.tipo_soporte')->label('Tipo de soporte')
                ->options([
                    'hdd_interno' => 'HDD interno', 'hdd_externo' => 'HDD externo',
                    'usb' => 'USB', 'servidor' => 'Servidor', 'nube' => 'Nube',
                    'dvd' => 'DVD', 'expediente_fisico' => 'Expediente físico', 'otro' => 'Otro',
                ])->required(),
            TextInput::make('datos.ubicacion')->label('Ubicación')->required(),
            Textarea::make('datos.contenido')->label('Contenido')->rows(3)->required(),
            DatePicker::make('datos.fecha_inventariado')->label('Fecha de inventariado')->required(),
        ];
    }

    private static function f08(): array
    {
        return [
            TextInput::make('datos.codigo_soporte')->label('Código soporte')->required(),
            Select::make('datos.tipo_movimiento')->label('Tipo de movimiento')
                ->options(['ingreso' => 'Ingreso', 'salida' => 'Salida', 'devolucion' => 'Devolución'])->required(),
            DateTimePicker::make('datos.fecha_hora')->label('Fecha / Hora')->required(),
            TextInput::make('datos.id_serie')->label('ID / Serie'),
            Select::make('datos.estado_soporte')->label('Estado')
                ->options(['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'])->required(),
            TextInput::make('datos.banco_datos')->label('Banco de datos'),
            Textarea::make('datos.contenido')->label('Contenido')->rows(2),
            TextInput::make('datos.origen_remitente')->label('Origen / Remitente'),
            TextInput::make('datos.destinatario')->label('Destinatario'),
            Textarea::make('datos.finalidad')->label('Finalidad')->rows(2),
            TextInput::make('datos.medio_transporte')->label('Medio de transporte'),
            Textarea::make('datos.precauciones')->label('Precauciones')->rows(2),
            TextInput::make('datos.autoriza')->label('Autoriza'),
            TextInput::make('datos.recibe')->label('Recibe'),
        ];
    }

    private static function f09(): array
    {
        return [
            DateTimePicker::make('datos.fecha_evento')->label('Fecha / Hora del evento')->required(),
            Select::make('datos.tipo_incidencia')->label('Tipo de incidencia')
                ->options([
                    'acceso_no_autorizado' => 'Acceso no autorizado', 'perdida_datos' => 'Pérdida de datos',
                    'fuga_informacion' => 'Fuga de información', 'malware' => 'Malware/Virus',
                    'fallo_sistema' => 'Fallo de sistema', 'otro' => 'Otro',
                ])->required(),
            TextInput::make('datos.sistema_equipo')->label('Sistema / Equipo / Lugar'),
            TextInput::make('datos.banco_datos')->label('Banco de datos'),
            RichEditor::make('datos.descripcion')->label('Descripción')->required(),
            Textarea::make('datos.medidas_inmediatas')->label('Medidas inmediatas')->rows(3),
            TagsInput::make('datos.personas_notificadas')->label('Personas notificadas'),
            Textarea::make('datos.impacto_potencial')->label('Impacto potencial')->rows(2),
            TextInput::make('datos.comunica_nombre')->label('Comunica (nombre y cargo)'),
            Select::make('datos.severidad')->label('Severidad')
                ->options(['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'])->required(),
        ];
    }

    private static function f10(): array
    {
        return [
            TextInput::make('datos.incidencia_ref')->label('N° Incidencia (referencia F09)')->required(),
            DateTimePicker::make('datos.fecha_cierre')->label('Fecha / Hora de cierre')->required(),
            Select::make('datos.clasificacion')->label('Clasificación')
                ->options(['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'])->required(),
            Toggle::make('datos.requirio_recuperacion')->label('Requirió recuperación'),
            Textarea::make('datos.medidas_adoptadas')->label('Medidas adoptadas')->rows(3)->required(),
            Textarea::make('datos.resultado_verificacion')->label('Resultado / Verificación')->rows(3),
            TextInput::make('datos.ejecuto')->label('Ejecutó (nombre y cargo)'),
            TextInput::make('datos.firma_responsable')->label('Firma responsable de seguridad'),
            Textarea::make('datos.acciones_preventivas')->label('Acciones preventivas')->rows(3),
        ];
    }

    private static function f11(): array
    {
        return [
            TextInput::make('datos.incidencia_relacionada')->label('Incidencia relacionada'),
            DateTimePicker::make('datos.fecha_realizacion')->label('Fecha / Hora de realización')->required(),
            Toggle::make('datos.autorizacion_escrita')->label('Autorización por escrito'),
            TextInput::make('datos.responsable_bd')->label('Responsable BD que autoriza'),
            Textarea::make('datos.proceso_realizado')->label('Proceso realizado')->rows(3)->required(),
            TextInput::make('datos.persona_ejecutora')->label('Persona ejecutora'),
            Textarea::make('datos.observaciones')->label('Observaciones')->rows(3),
        ];
    }

    private static function f12(): array
    {
        return [
            TextInput::make('datos.nombre_backup')->label('Nombre del backup')->required(),
            Textarea::make('datos.descripcion_contenido')->label('Descripción del contenido')->rows(3)->required(),
            DatePicker::make('datos.fecha_copia')->label('Fecha de copia')->required(),
            Select::make('datos.periodicidad')->label('Periodicidad')
                ->options([
                    'diaria' => 'Diaria', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal',
                    'mensual' => 'Mensual', 'trimestral' => 'Trimestral', 'puntual' => 'Puntual',
                ])->required(),
        ];
    }

    private static function f13(): array
    {
        return [
            DatePicker::make('datos.fecha_destruccion')->label('Fecha de destrucción')->required(),
            Textarea::make('datos.descripcion_activo')->label('Descripción del activo')->rows(3)->required(),
            Select::make('datos.metodo')->label('Método de destrucción')
                ->options([
                    'borrado_seguro' => 'Borrado seguro', 'destruccion_fisica' => 'Destrucción física',
                    'desmagnetizacion' => 'Desmagnetización', 'incineracion' => 'Incineración',
                    'trituracion' => 'Trituración', 'proveedor_certificado' => 'Proveedor certificado',
                ])->required(),
            TextInput::make('datos.responsable')->label('Responsable')->required(),
            TextInput::make('datos.autoriza')->label('Autoriza')->required(),
            DatePicker::make('datos.proxima_revision')->label('Próxima revisión'),
        ];
    }
}
