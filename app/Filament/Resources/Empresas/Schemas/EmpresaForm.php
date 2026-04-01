<?php

namespace App\Filament\Resources\Empresas\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la empresa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('ruc')
                            ->label('RUC')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->minLength(11)
                            ->maxLength(11)
                            ->regex('/^\d{11}$/')
                            ->validationMessages([
                                'regex' => 'El RUC debe contener exactamente 11 dígitos numéricos.',
                            ]),
                        TextInput::make('razon_social')
                            ->label('Razón social')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(150),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),
                Section::make('Personalización del Portal')
                    ->description('Configura la apariencia del portal para esta empresa')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo de la empresa')
                            ->image()
                            ->disk('logos')
                            ->directory('/')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml']),
                        TextInput::make('nombre_portal')
                            ->label('Nombre visible en el portal')
                            ->maxLength(100)
                            ->placeholder('Ej: Estudio Palacios'),
                        ColorPicker::make('color_primario')
                            ->label('Color primario (header/botones)'),
                        ColorPicker::make('color_secundario')
                            ->label('Color secundario (acentos)'),
                        ColorPicker::make('color_sidebar')
                            ->label('Color del sidebar'),
                    ]),
                Section::make('Configuración')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required(),
                    ]),
            ]);
    }
}
