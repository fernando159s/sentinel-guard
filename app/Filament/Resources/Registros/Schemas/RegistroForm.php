<?php

namespace App\Filament\Resources\Registros\Schemas;

use App\Enums\TipoFormato;
use App\Services\FormatoFieldsService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class RegistroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tipo de registro')
                    ->icon('heroicon-o-document-text')
                    ->description('Selecciona el formato de seguridad que deseas registrar.')
                    ->schema([
                        Select::make('tipo_formato')
                            ->label('Formato de seguridad')
                            ->options(TipoFormato::options())
                            ->required()
                            ->live()
                            ->searchable()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->columnSpanFull(),
                        Placeholder::make('formato_info')
                            ->label('')
                            ->content(function (Get $get): ?HtmlString {
                                $tipo = TipoFormato::tryFrom($get('tipo_formato') ?? '');
                                if (! $tipo) {
                                    return null;
                                }

                                return new HtmlString(
                                    '<div class="rounded-lg border border-primary-200 bg-primary-50 p-3 dark:border-primary-800 dark:bg-primary-950/50">'
                                    . '<p class="text-sm font-semibold text-primary-700 dark:text-primary-400">' . e($tipo->label()) . '</p>'
                                    . '<p class="mt-1 text-xs text-primary-600 dark:text-primary-500">' . e($tipo->description()) . '</p>'
                                    . '<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Politica: ' . e($tipo->psc()) . '</p>'
                                    . '</div>'
                                );
                            })
                            ->visible(fn (Get $get): bool => filled($get('tipo_formato')))
                            ->columnSpanFull(),
                        TextInput::make('numero_registro')
                            ->label('N° Registro')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se genera automaticamente al guardar.')
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make(fn (Get $get): string => self::getSectionTitle($get('tipo_formato')))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->description(fn (Get $get): ?string => self::getSectionDescription($get('tipo_formato')))
                    ->schema(fn (Get $get): array => self::getDynamicFields($get('tipo_formato')))
                    ->visible(fn (Get $get): bool => filled($get('tipo_formato')))
                    ->columns(2)
                    ->collapsible(),
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

    private static function getSectionTitle(?string $tipo): string
    {
        $tipoEnum = TipoFormato::tryFrom($tipo ?? '');

        return $tipoEnum ? 'Datos — ' . $tipoEnum->label() : 'Datos del formato';
    }

    private static function getSectionDescription(?string $tipo): ?string
    {
        $tipoEnum = TipoFormato::tryFrom($tipo ?? '');

        return $tipoEnum ? 'Completa los campos requeridos para el formato ' . $tipoEnum->value . '.' : null;
    }
}
