<?php

namespace App\Filament\Resources\Equipos\Schemas;

use App\Models\ChecklistEjecucion;
use App\Models\Equipo;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class EquipoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificacion del equipo')
                    ->icon('heroicon-o-computer-desktop')
                    ->description('Datos basicos del activo. El codigo interno es el identificador de tu empresa.')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo de equipo')
                            ->options([
                                'Tecnologicos' => [
                                    'pc_escritorio' => 'PC de escritorio',
                                    'laptop' => 'Laptop',
                                    'impresora' => 'Impresora',
                                    'servidor' => 'Servidor',
                                    'usb' => 'Dispositivo USB',
                                    'disco_externo' => 'Disco externo',
                                    'telefono' => 'Telefono',
                                    'tablet' => 'Tablet',
                                    'dispositivo_red' => 'Dispositivo de red',
                                ],
                                'No tecnologicos' => [
                                    'dvd_cd' => 'DVD / CD',
                                    'expediente_fisico' => 'Expediente fisico',
                                    'soporte_nube' => 'Servicio cloud',
                                    'otro' => 'Otro',
                                ],
                            ])
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('codigo_interno')
                            ->label('Codigo interno')
                            ->placeholder('EQ-001')
                            ->helperText('Codigo de inventario de tu empresa'),
                        TextInput::make('numero_serie')
                            ->label('Numero de serie')
                            ->unique(ignoreRecord: true),
                        TextInput::make('marca')
                            ->label('Marca')
                            ->placeholder('Lenovo, HP, Dell...'),
                        TextInput::make('modelo')
                            ->label('Modelo')
                            ->placeholder('ThinkPad T14, ProBook 450...'),
                    ]),

                Section::make('Clasificacion de soporte')
                    ->icon('heroicon-o-archive-box')
                    ->description('Categoriza el activo segun el tipo de soporte de informacion.')
                    ->columns(3)
                    ->schema([
                        Select::make('categoria')
                            ->label('Categoria')
                            ->options([
                                'tecnologico' => 'Tecnologico',
                                'no_tecnologico' => 'No tecnologico',
                            ])
                            ->default('tecnologico')
                            ->required()
                            ->live(),
                        Select::make('clasificacion_soporte')
                            ->label('Clasificacion de soporte')
                            ->options([
                                'hdd_interno' => 'HDD interno',
                                'hdd_externo' => 'HDD externo',
                                'usb' => 'USB',
                                'servidor' => 'Servidor',
                                'nube' => 'Nube',
                                'dvd' => 'DVD / CD',
                                'expediente_fisico' => 'Expediente fisico',
                                'otro' => 'Otro',
                            ]),
                        Textarea::make('contenido_datos')
                            ->label('Contenido de datos')
                            ->placeholder('Descripcion de los datos que almacena este activo')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Especificaciones tecnicas')
                    ->icon('heroicon-o-cpu-chip')
                    ->description('Detalles del hardware. Solo aplica para equipos tecnologicos.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('categoria') === 'tecnologico' && in_array($get('tipo'), ['pc_escritorio', 'laptop', 'servidor', 'tablet', 'telefono', 'dispositivo_red']))
                    ->schema([
                        TextInput::make('sistema_operativo')
                            ->label('Sistema operativo')
                            ->placeholder('Windows 11 Pro, Ubuntu 22.04...'),
                        TextInput::make('procesador')
                            ->label('Procesador')
                            ->placeholder('Intel i7-1365U, AMD Ryzen 5...'),
                        TextInput::make('ram_gb')
                            ->label('RAM (GB)')
                            ->numeric()
                            ->suffix('GB'),
                        TextInput::make('disco_gb')
                            ->label('Disco (GB)')
                            ->numeric()
                            ->suffix('GB'),
                    ]),

                Section::make('Ubicacion y clasificacion')
                    ->icon('heroicon-o-map-pin')
                    ->description('Donde esta el equipo y que tan sensible es la informacion que maneja.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('ubicacion')
                            ->label('Ubicacion')
                            ->placeholder('Oficina principal - Piso 2')
                            ->columnSpanFull(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'mantenimiento' => 'En mantenimiento',
                                'obsoleto' => 'Obsoleto',
                                'dado_de_baja' => 'Dado de baja',
                            ])
                            ->default('activo')
                            ->required(),
                        Select::make('nivel_sensibilidad')
                            ->label('Nivel de sensibilidad')
                            ->options([
                                'publico' => 'Publico',
                                'interno' => 'Interno',
                                'confidencial' => 'Confidencial',
                                'sensible' => 'Sensible',
                            ])
                            ->default('interno')
                            ->required()
                            ->helperText('Segun la clasificacion de datos que maneja'),
                        DatePicker::make('fecha_adquisicion')
                            ->label('Fecha de adquisicion'),
                        DatePicker::make('fecha_garantia')
                            ->label('Vencimiento de garantia'),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Notas adicionales')
                            ->rows(3)
                            ->placeholder('Software instalado, accesorios incluidos, etc.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Historial de asignaciones')
                    ->icon('heroicon-o-clock')
                    ->description('Registro de todas las asignaciones, transferencias y devoluciones.')
                    ->schema([
                        Placeholder::make('asignacion_actual')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record instanceof Equipo) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Guarda el equipo primero para ver el historial.</p>');
                                }

                                $vigente = $record->asignacionVigente;
                                $currentHtml = $vigente
                                    ? '<div class="rounded-lg border border-success-200 bg-success-50 p-3 dark:border-success-800 dark:bg-success-950/50 mb-4">'
                                      . '<p class="text-sm font-semibold text-success-700 dark:text-success-400">Asignado a: ' . e($vigente->user?->name ?? '—') . '</p>'
                                      . '<p class="text-xs text-success-600 dark:text-success-500">Desde: ' . $vigente->fecha_inicio->format('d/m/Y H:i') . ' | Condicion: ' . e($vigente->condicion_entrega ?? '—') . '</p>'
                                      . '</div>'
                                    : '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800 mb-4">'
                                      . '<p class="text-sm text-gray-500 dark:text-gray-400">Sin asignar</p></div>';

                                $asignaciones = $record->asignaciones()
                                    ->with(['user', 'asignador'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

                                if ($asignaciones->isEmpty()) {
                                    return new HtmlString($currentHtml . '<p class="text-sm text-gray-500">Sin historial.</p>');
                                }

                                $rows = '';
                                foreach ($asignaciones as $a) {
                                    $tipoColor = match ($a->tipo) {
                                        'asignacion' => 'success',
                                        'transferencia' => 'warning',
                                        'devolucion' => 'gray',
                                        'baja' => 'danger',
                                        'ingreso_nuevo' => 'info',
                                        'salida_mantenimiento' => 'warning',
                                        'salida_homeoffice' => 'info',
                                        'salida_terceros' => 'danger',
                                        default => 'gray',
                                    };
                                    $tipoLabel = match ($a->tipo) {
                                        'asignacion' => 'Asignacion',
                                        'transferencia' => 'Transferencia',
                                        'devolucion' => 'Devolucion',
                                        'baja' => 'Baja',
                                        'ingreso_nuevo' => 'Ingreso nuevo',
                                        'salida_mantenimiento' => 'Mantenimiento',
                                        'salida_homeoffice' => 'Home office',
                                        'salida_terceros' => 'A terceros',
                                        default => $a->tipo,
                                    };
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-2 px-2 text-xs"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-' . $tipoColor . '-100 text-' . $tipoColor . '-700 dark:bg-' . $tipoColor . '-500/20 dark:text-' . $tipoColor . '-400">' . $tipoLabel . '</span></td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-700 dark:text-gray-300">' . e($a->user?->name ?? '—') . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . $a->fecha_inicio->format('d/m/Y H:i') . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . ($a->fecha_fin?->format('d/m/Y H:i') ?? '—') . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . e($a->condicion_entrega ?? '') . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . e($a->asignador?->name ?? '—') . '</td>'
                                        . '</tr>';
                                }

                                $table = '<table class="w-full text-left">'
                                    . '<thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Tipo</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Usuario</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Inicio</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Fin</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Condicion</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Realizado por</th>'
                                    . '</tr></thead><tbody>' . $rows . '</tbody></table>';

                                return new HtmlString($currentHtml . $table);
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible(),

                Section::make('Historial de checklists')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->description('Verificaciones de cumplimiento realizadas en este equipo.')
                    ->schema([
                        Placeholder::make('checklist_historial')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record instanceof Equipo) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Guarda el equipo primero.</p>');
                                }

                                $ejecuciones = ChecklistEjecucion::where('equipo_id', $record->id)
                                    ->with(['plantilla', 'ejecutor'])
                                    ->orderBy('fecha_ejecucion', 'desc')
                                    ->limit(10)
                                    ->get();

                                if ($ejecuciones->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Sin checklists ejecutados. Usa el boton "Checklist" para ejecutar uno.</p>');
                                }

                                $rows = '';
                                foreach ($ejecuciones as $e) {
                                    $estadoColor = match ($e->estado) {
                                        'completo' => 'success',
                                        'con_observaciones' => 'warning',
                                        default => 'gray',
                                    };
                                    $cumple = $e->itemsCumplen();
                                    $total = $e->totalItems();
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-2 px-2 text-xs text-gray-700 dark:text-gray-300">' . e($e->plantilla?->nombre ?? '—') . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . $e->fecha_ejecucion->format('d/m/Y H:i') . '</td>'
                                        . '<td class="py-2 px-2 text-xs"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-' . $estadoColor . '-100 text-' . $estadoColor . '-700 dark:bg-' . $estadoColor . '-500/20 dark:text-' . $estadoColor . '-400">' . $cumple . '/' . $total . '</span></td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . e($e->ejecutor?->name ?? '—') . '</td>'
                                        . '</tr>';
                                }

                                return new HtmlString(
                                    '<table class="w-full text-left">'
                                    . '<thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Checklist</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Fecha</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Resultado</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Ejecutado por</th>'
                                    . '</tr></thead><tbody>' . $rows . '</tbody></table>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible(),
            ]);
    }
}
