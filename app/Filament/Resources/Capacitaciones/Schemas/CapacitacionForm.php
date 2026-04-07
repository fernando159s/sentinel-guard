<?php

namespace App\Filament\Resources\Capacitaciones\Schemas;

use App\Enums\ModalidadCapacitacion;
use App\Models\Registro;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CapacitacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Datos de la capacitacion')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('tema')
                            ->label('Tema')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('descripcion')
                            ->label('Descripcion')
                            ->rows(3)
                            ->columnSpanFull(),
                        DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required(),
                        TimePicker::make('hora_inicio')
                            ->label('Hora de inicio')
                            ->required()
                            ->seconds(false),
                        TextInput::make('duracion_minutos')
                            ->label('Duracion')
                            ->numeric()
                            ->required()
                            ->suffix('minutos')
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(60),
                        Select::make('modalidad')
                            ->label('Modalidad')
                            ->options(collect(ModalidadCapacitacion::cases())
                                ->mapWithKeys(fn ($m) => [$m->value => $m->label()]))
                            ->required(),
                        TextInput::make('expositor')
                            ->label('Expositor / Instructor')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Vinculo con registro de auditoria')
                    ->icon('heroicon-o-link')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('registro_id')
                            ->label('Registro F01 (Auditoria)')
                            ->options(function () {
                                return Registro::withoutGlobalScopes()
                                    ->where('empresa_id', Filament::getTenant()?->id)
                                    ->where('tipo_formato', 'F01')
                                    ->pluck('numero_registro', 'id');
                            })
                            ->searchable()
                            ->helperText('Vincular opcionalmente a un registro de auditoria (F01).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
