<?php

namespace App\Filament\Resources\Registros\Schemas;

use App\Enums\TipoFormato;
use App\Services\FormatoFieldsService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
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
                // Full-width catalog when no format selected (create only)
                Section::make('Selecciona un formato de seguridad')
                    ->icon('heroicon-o-document-text')
                    ->description('Elige el tipo de registro que deseas crear. Cada formato corresponde a una politica de seguridad.')
                    ->schema([
                        Placeholder::make('formato_catalog')
                            ->label('')
                            ->hiddenLabel()
                            ->content(fn () => new HtmlString(self::buildCatalogHtml()))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && ! filled($get('tipo_formato')))
                    ->columnSpanFull(),

                // Hidden select to hold the value
                Select::make('tipo_formato')
                    ->label('Formato')
                    ->options(TipoFormato::options())
                    ->required()
                    ->live()
                    ->searchable()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated()
                    ->hidden(fn (string $operation): bool => $operation === 'create')
                    ->columnSpanFull(),

                // 2-column layout when format IS selected
                Grid::make([
                    'default' => 1,
                    'lg' => 7,
                ])
                ->visible(fn (Get $get): bool => filled($get('tipo_formato')))
                ->schema([
                    // Left column — selected format + mini selector
                    Section::make('Formato seleccionado')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Placeholder::make('formato_selected')
                                ->label('')
                                ->hiddenLabel()
                                ->content(function (Get $get): ?HtmlString {
                                    $tipo = TipoFormato::tryFrom($get('tipo_formato') ?? '');
                                    if (! $tipo) {
                                        return null;
                                    }

                                    $html = '<div class="rounded-lg border border-primary-500 dark:border-primary-500 bg-primary-50/50 dark:bg-primary-500/5 p-4 mb-3">'
                                        . '<div class="flex items-center gap-2 mb-2">'
                                        . '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-500/20 text-xs font-bold text-primary-700 dark:text-primary-300">' . e($tipo->value) . '</span>'
                                        . '<p class="text-sm font-bold text-gray-900 dark:text-white">' . e(str_replace($tipo->value . ' - ', '', $tipo->label())) . '</p>'
                                        . '</div>'
                                        . '<p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">' . e($tipo->description()) . '</p>'
                                        . '<p class="mt-2 text-[11px] font-medium text-gray-400 dark:text-gray-500">Politica: ' . e($tipo->psc()) . '</p>'
                                        . '</div>'
                                        . '<button type="button" wire:click="$set(\'data.tipo_formato\', null)" '
                                        . 'class="w-full text-center text-xs text-primary-600 dark:text-primary-400 hover:underline cursor-pointer py-1">'
                                        . '← Cambiar formato</button>';

                                    return new HtmlString($html);
                                })
                                ->visible(fn (string $operation): bool => $operation === 'create')
                                ->columnSpanFull(),
                            TextInput::make('numero_registro')
                                ->label('N° Registro')
                                ->disabled()
                                ->dehydrated()
                                ->helperText('Se genera automaticamente al guardar.')
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->columnSpan(['default' => 1, 'lg' => 2]),

                    // Right column — dynamic form
                    Section::make(fn (Get $get): string => self::getSectionTitle($get('tipo_formato')))
                        ->icon('heroicon-o-clipboard-document-list')
                        ->description(fn (Get $get): ?string => self::getSectionDescription($get('tipo_formato')))
                        ->schema(fn (Get $get): array => self::getDynamicFields($get('tipo_formato')))
                        ->columns(2)
                        ->collapsible()
                        ->columnSpan(['default' => 1, 'lg' => 5]),

                    // Equipo vinculado (solo F13)
                    Section::make('Equipo vinculado')
                        ->icon('heroicon-o-computer-desktop')
                        ->description('Vincula este registro al equipo correspondiente.')
                        ->schema([
                            Select::make('equipo_id')
                                ->label('Equipo')
                                ->relationship('equipo', 'codigo_interno')
                                ->searchable()
                                ->placeholder('Seleccionar equipo (opcional)'),
                        ])
                        ->visible(fn (Get $get): bool => $get('tipo_formato') === 'F13')
                        ->columnSpan(['default' => 1, 'lg' => 7]),
                ]),
            ]);
    }

    private static function buildCatalogHtml(): string
    {
        $groups = [
            'Datos y acceso' => [TipoFormato::F02, TipoFormato::F04, TipoFormato::F05, TipoFormato::F03],
            'Soportes y activos' => [TipoFormato::F12, TipoFormato::F13], // F07 y F08 son ahora reportes, no registros
            'Incidencias' => [TipoFormato::F09, TipoFormato::F10, TipoFormato::F11],
            'Auditoria y control' => [TipoFormato::F01, TipoFormato::F06],
        ];

        $icons = [
            'Datos y acceso' => '&#x1F4BE;',
            'Soportes y activos' => '&#x1F4E6;',
            'Incidencias' => '&#x26A0;',
            'Auditoria y control' => '&#x1F50D;',
        ];

        $html = '<style>'
            . '.fmt-card{cursor:pointer;border-radius:12px;border:1px solid rgba(255,255,255,0.08);padding:20px;transition:all 0.15s ease;position:relative;overflow:hidden;}'
            . '.fmt-card:hover{border-color:#8b5cf6;background:rgba(139,92,246,0.04);}'
            . '.fmt-card:active{opacity:0.85;}'
            . '.fmt-card:hover .fmt-badge{background:rgba(139,92,246,0.2);color:#c4b5fd;}'
            . '.fmt-card:hover .fmt-title{color:#c4b5fd;}'
            . '</style>'
            . '<div x-data="{ search: \'\' }" style="display:flex;flex-direction:column;gap:40px;padding:8px 0;">'
            . '<div style="position:sticky;top:0;z-index:10;padding-bottom:8px;">'
            . '<label class="text-sm font-medium text-gray-700 dark:text-gray-300" style="display:block;margin-bottom:6px;">Buscar formato</label>'
            . '<div style="position:relative;">'
            . '<svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;" class="text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>'
            . '<input type="text" x-model="search" placeholder="Escribe el codigo, nombre o palabra clave..." '
            . 'class="w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition" style="padding-left:36px;padding-right:16px;" />'
            . '</div>'
            . '</div>';

        foreach ($groups as $groupName => $formatos) {
            $icon = $icons[$groupName] ?? '';
            $searchTerms = collect($formatos)->map(fn ($t) => strtolower($t->value . ' ' . $t->label() . ' ' . $t->description() . ' ' . $t->psc()));
            $html .= '<div x-show="!' . "search || " . collect($formatos)->map(fn ($t) => "'" . strtolower($t->value . ' ' . str_replace("'", "", $t->label()) . ' ' . str_replace("'", "", $t->description())) . "'.includes(search.toLowerCase())")->implode(' || ') . '" x-cloak>'
                . '<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,0.05);">'
                . '<span style="font-size:16px;">' . $icon . '</span>'
                . '<h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">' . e($groupName) . '</h3>'
                . '<span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">' . count($formatos) . ' formatos</span>'
                . '</div>'
                . '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">';

            foreach ($formatos as $tipo) {
                $searchStr = strtolower($tipo->value . ' ' . str_replace("'", "", $tipo->label()) . ' ' . str_replace("'", "", $tipo->description()));
                $html .= '<div wire:click="$set(\'data.tipo_formato\', \'' . $tipo->value . '\')" '
                    . 'x-show="!search || \'' . $searchStr . '\'.includes(search.toLowerCase())" x-cloak '
                    . 'class="fmt-card">'
                    // Badge code top-right
                    . '<div style="position:absolute;top:16px;right:16px;">'
                    . '<span class="fmt-badge" style="display:inline-flex;align-items:center;border-radius:6px;background:rgba(255,255,255,0.05);padding:2px 8px;font-size:10px;font-weight:700;color:rgba(255,255,255,0.4);transition:all 0.15s;">' . e($tipo->value) . '</span>'
                    . '</div>'
                    // Title
                    . '<p class="fmt-title" style="font-size:14px;font-weight:700;color:#fff;line-height:1.3;padding-right:50px;transition:color 0.15s;">' . e(str_replace($tipo->value . ' - ', '', $tipo->label())) . '</p>'
                    // Description
                    . '<p style="font-size:12px;color:rgba(255,255,255,0.45);line-height:1.6;margin-top:10px;">' . e($tipo->description()) . '</p>'
                    // Policy footer
                    . '<div style="display:flex;align-items:center;gap:6px;margin-top:14px;">'
                    . '<svg style="width:12px;height:12px;color:rgba(255,255,255,0.3);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>'
                    . '<span style="font-size:10px;font-weight:500;color:rgba(255,255,255,0.3);">' . e($tipo->psc()) . '</span>'
                    . '</div>'
                    . '</div>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
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
