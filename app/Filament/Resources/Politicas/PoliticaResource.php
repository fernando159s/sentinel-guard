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
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informacion de la politica')
                    ->icon('heroicon-o-document-text')
                    ->description('Define el titulo y contenido de la politica o NDA.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Titulo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('version')
                            ->label('Version')
                            ->default('1.0')
                            ->required()
                            ->helperText('Incrementa la version al hacer cambios importantes.'),
                        Toggle::make('obligatoria')
                            ->label('Obligatoria')
                            ->helperText('Los usuarios no podran usar el sistema sin aceptar.')
                            ->default(true)
                            ->inline(false),
                        Toggle::make('activa')
                            ->label('Activa')
                            ->default(true)
                            ->inline(false),
                    ])->columns(4),

                Section::make('Contenido de la politica')
                    ->icon('heroicon-o-pencil-square')
                    ->description('Redacta el texto completo del NDA o politica. Este es el texto que los usuarios veran y deberan aceptar.')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('contenido')
                            ->label('')
                            ->required()
                            ->columnSpanFull(),
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
            ])
            ->recordActions([
                Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('politicas.pdf', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
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
