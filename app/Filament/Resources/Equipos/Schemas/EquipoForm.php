<?php

namespace App\Filament\Resources\Equipos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                                'pc_escritorio' => 'PC de escritorio',
                                'laptop' => 'Laptop',
                                'impresora' => 'Impresora',
                                'servidor' => 'Servidor',
                                'usb' => 'Dispositivo USB',
                                'otro' => 'Otro',
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

                Section::make('Especificaciones tecnicas')
                    ->icon('heroicon-o-cpu-chip')
                    ->description('Detalles del hardware. Solo aplica para PCs, laptops y servidores.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('tipo'), ['pc_escritorio', 'laptop', 'servidor']))
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
            ]);
    }
}
