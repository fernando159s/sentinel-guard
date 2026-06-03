<?php

namespace App\Filament\Resources\ActivosDigitales\Schemas;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ActivoDigitalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificacion de la cuenta')
                    ->icon('heroicon-o-identification')
                    ->description('Datos basicos del activo digital. El codigo interno se genera automaticamente.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->placeholder('WhatsApp Ventas, Meta Business Studio...')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('tipo')
                            ->label('Tipo de activo')
                            ->options(TipoActivoDigital::options())
                            ->required()
                            ->native(false),
                        TextInput::make('proveedor')
                            ->label('Proveedor')
                            ->placeholder('Meta, Google, OpenAI, GoDaddy...')
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('URL de acceso')
                            ->placeholder('https://business.facebook.com')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('identificador')
                            ->label('Identificador')
                            ->placeholder('Nro de telefono, email, business ID...')
                            ->helperText('Como se identifica la cuenta en el proveedor')
                            ->maxLength(255),
                        TextInput::make('codigo_interno')
                            ->label('Codigo interno')
                            ->placeholder('AD-001 (automatico)')
                            ->helperText('Se genera solo si lo dejas vacio')
                            ->maxLength(255),
                    ]),

                Section::make('Cobro y vigencia')
                    ->icon('heroicon-o-credit-card')
                    ->description('Modalidad de pago y control de renovacion.')
                    ->columns(3)
                    ->schema([
                        Select::make('modalidad_pago')
                            ->label('Modalidad de pago')
                            ->options(ModalidadPago::options())
                            ->default(ModalidadPago::Mensual->value)
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('costo')
                            ->label('Costo del periodo')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->prefix(fn (Get $get): string => $get('moneda') ?: 'PEN')
                            ->visible(fn (Get $get): bool => $get('modalidad_pago') !== ModalidadPago::Gratuito->value),
                        Select::make('moneda')
                            ->label('Moneda')
                            ->options([
                                'PEN' => 'PEN — Soles',
                                'USD' => 'USD — Dolares',
                                'EUR' => 'EUR — Euros',
                            ])
                            ->default('PEN')
                            ->required()
                            ->native(false)
                            ->visible(fn (Get $get): bool => $get('modalidad_pago') !== ModalidadPago::Gratuito->value),
                        TextInput::make('metodo_pago')
                            ->label('Metodo de pago')
                            ->placeholder('Visa ***1234, transferencia, PayPal...')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('modalidad_pago') !== ModalidadPago::Gratuito->value),
                        DatePicker::make('fecha_adquisicion')
                            ->label('Fecha de adquisicion'),
                        DatePicker::make('fecha_vencimiento')
                            ->label('Proximo vencimiento')
                            ->helperText('Cuando vence/renueva la suscripcion')
                            ->visible(fn (Get $get): bool => in_array($get('modalidad_pago'), [
                                ModalidadPago::Mensual->value,
                                ModalidadPago::Anual->value,
                            ], true)),
                        Toggle::make('renovacion_automatica')
                            ->label('Renovacion automatica')
                            ->inline(false)
                            ->visible(fn (Get $get): bool => in_array($get('modalidad_pago'), [
                                ModalidadPago::Mensual->value,
                                ModalidadPago::Anual->value,
                            ], true)),
                    ]),

                Section::make('Gestion y clasificacion')
                    ->icon('heroicon-o-shield-check')
                    ->description('Responsable, estado y sensibilidad de la informacion.')
                    ->columns(3)
                    ->schema([
                        Select::make('responsables')
                            ->label('Responsables')
                            ->relationship('responsables', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Las personas que pueden ver las credenciales de esta cuenta')
                            ->disabled(fn (): bool => ! (auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false))
                            ->columnSpanFull(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoActivoDigital::options())
                            ->default(EstadoActivoDigital::Activo->value)
                            ->required()
                            ->native(false),
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
                            ->native(false),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Notas adicionales')
                            ->rows(3)
                            ->placeholder('Detalles del activo, alcance, accesos relacionados, etc.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
