<?php

namespace App\Filament\Resources\Backups\Schemas;

use App\Models\BackupEjecucion;
use App\Models\BackupProgramacion;
use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class BackupProgramacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Programacion de backup')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->description('Define el nombre, equipo asociado, periodicidad y proxima fecha de ejecucion.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del backup')
                            ->required()
                            ->placeholder('Backup BD produccion, Backup archivos legales...')
                            ->columnSpanFull(),
                        Select::make('equipo_id')
                            ->label('Equipo asociado (opcional)')
                            ->options(fn () => Equipo::withoutGlobalScopes()
                                ->where('empresa_id', Filament::getTenant()?->id)
                                ->whereNotIn('estado', ['dado_de_baja'])
                                ->pluck('codigo_interno', 'id'))
                            ->searchable()
                            ->placeholder('General (sin equipo especifico)'),
                        Select::make('periodicidad')
                            ->label('Periodicidad')
                            ->options([
                                'diaria' => 'Diaria',
                                'semanal' => 'Semanal',
                                'quincenal' => 'Quincenal',
                                'mensual' => 'Mensual',
                                'trimestral' => 'Trimestral',
                                'puntual' => 'Puntual (una vez)',
                            ])
                            ->required(),
                        DatePicker::make('proximo_backup')
                            ->label('Proxima fecha de backup')
                            ->required(),
                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true)
                            ->inline(false),
                        Textarea::make('descripcion')
                            ->label('Descripcion')
                            ->rows(2)
                            ->placeholder('Que datos respalda, donde se almacena...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Historial de ejecuciones')
                    ->icon('heroicon-o-clock')
                    ->description('Registro de todas las ejecuciones de este backup.')
                    ->schema([
                        Placeholder::make('ejecuciones_historial')
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record instanceof BackupProgramacion) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Guarda la programacion primero para ver el historial.</p>');
                                }

                                $ejecuciones = BackupEjecucion::where('programacion_id', $record->id)
                                    ->with(['ejecutor', 'registro'])
                                    ->orderBy('fecha_ejecucion', 'desc')
                                    ->limit(20)
                                    ->get();

                                if ($ejecuciones->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Sin ejecuciones registradas. Usa el boton "Marcar ejecutado" para registrar una.</p>');
                                }

                                $rows = '';
                                foreach ($ejecuciones as $e) {
                                    $estadoColor = match ($e->estado) {
                                        'ejecutado' => 'success',
                                        'pendiente' => 'warning',
                                        'atrasado' => 'danger',
                                        default => 'gray',
                                    };
                                    $regLink = $e->registro
                                        ? '<a href="#" class="text-primary-600 dark:text-primary-400">' . e($e->registro->numero_registro) . '</a>'
                                        : '-';
                                    $rows .= '<tr class="border-b border-gray-100 dark:border-gray-800">'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . $e->fecha_ejecucion->format('d/m/Y H:i') . '</td>'
                                        . '<td class="py-2 px-2 text-xs"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-' . $estadoColor . '-100 text-' . $estadoColor . '-700 dark:bg-' . $estadoColor . '-500/20 dark:text-' . $estadoColor . '-400">' . ucfirst($e->estado) . '</span></td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-700 dark:text-gray-300">' . e($e->ejecutor?->name ?? '-') . '</td>'
                                        . '<td class="py-2 px-2 text-xs">' . $regLink . '</td>'
                                        . '<td class="py-2 px-2 text-xs text-gray-500">' . e(mb_substr($e->notas ?? '', 0, 50)) . '</td>'
                                        . '</tr>';
                                }

                                return new HtmlString(
                                    '<table class="w-full text-left">'
                                    . '<thead><tr class="border-b border-gray-200 dark:border-gray-700">'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Fecha</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Estado</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Ejecutado por</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Registro F12</th>'
                                    . '<th class="py-2 px-2 text-xs font-medium text-gray-500">Notas</th>'
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
