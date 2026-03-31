<?php

namespace App\Filament\Resources\Registros\Schemas;

use App\Enums\TipoFormato;
use App\Services\FormatoFieldsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tipo de registro')
                    ->schema([
                        Select::make('tipo_formato')
                            ->label('Formato de seguridad')
                            ->options(TipoFormato::options())
                            ->required()
                            ->live()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->helperText(fn (Get $get): ?string => self::getPscText($get('tipo_formato'))),
                        TextInput::make('numero_registro')
                            ->label('N° Registro')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se genera automáticamente al guardar.'),
                    ])->columns(2),

                Section::make('Datos del formato')
                    ->schema(fn (Get $get): array => self::getDynamicFields($get('tipo_formato')))
                    ->visible(fn (Get $get): bool => filled($get('tipo_formato')))
                    ->columns(2),
            ]);
    }

    private static function getDynamicFields(?string $tipo): array
    {
        if (! $tipo) {
            return [];
        }

        $tipoEnum = TipoFormato::tryFrom($tipo);

        if (! $tipoEnum) {
            return [];
        }

        return FormatoFieldsService::getFields($tipoEnum);
    }

    private static function getPscText(?string $tipo): ?string
    {
        if (! $tipo) {
            return null;
        }

        $tipoEnum = TipoFormato::tryFrom($tipo);

        return $tipoEnum ? 'Política: ' . $tipoEnum->psc() : null;
    }
}
