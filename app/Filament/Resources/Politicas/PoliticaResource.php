<?php

namespace App\Filament\Resources\Politicas;

use App\Filament\Resources\Politicas\Pages\CreatePolitica;
use App\Filament\Resources\Politicas\Pages\EditPolitica;
use App\Filament\Resources\Politicas\Pages\ListPoliticas;
use App\Models\Politica;
use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PoliticaResource extends Resource
{
    protected static ?string $model = Politica::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?string $modelLabel = 'Politica';

    protected static ?string $pluralModelLabel = 'Politicas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informacion de la politica')
                    ->icon('heroicon-o-document-text')
                    ->description('Define el titulo y contenido de la politica o NDA.')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Titulo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('version')
                            ->label('Version')
                            ->default('1.0')
                            ->required()
                            ->helperText('Incrementa la version al hacer cambios importantes para que los usuarios deban re-aceptar.'),
                        RichEditor::make('contenido')
                            ->label('Contenido completo')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Configuracion')
                    ->schema([
                        Toggle::make('obligatoria')
                            ->label('Obligatoria')
                            ->helperText('Si esta activa, los usuarios no podran usar el sistema sin aceptar esta politica.')
                            ->default(true),
                        Toggle::make('activa')
                            ->label('Activa')
                            ->helperText('Las politicas inactivas no se muestran a los usuarios.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                IconColumn::make('obligatoria')
                    ->label('Obligatoria')
                    ->boolean(),
                IconColumn::make('activa')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('aceptaciones_count')
                    ->label('Aceptaciones')
                    ->counts('aceptaciones')
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('activa')
                    ->options(['1' => 'Activas', '0' => 'Inactivas']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPoliticas::route('/'),
            'create' => CreatePolitica::route('/create'),
            'edit' => EditPolitica::route('/{record}/edit'),
        ];
    }
}
