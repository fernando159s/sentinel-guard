<?php

namespace App\Filament\Resources\Politicas;

use App\Filament\Resources\Politicas\Pages\CreatePolitica;
use App\Filament\Resources\Politicas\Pages\EditPolitica;
use App\Filament\Resources\Politicas\Pages\ListPoliticas;
use App\Filament\Resources\Politicas\Pages\ViewNdaFirmantes;
use App\Filament\Resources\Politicas\RelationManagers\VersionesRelationManager;
use App\Models\Politica;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                            ->inline(false)
                            ->disabled(fn (Get $get) => $get('es_nda'))
                            ->dehydrated(true),
                        Toggle::make('activa')
                            ->label('Activa')
                            ->default(true)
                            ->inline(false),
                        Toggle::make('es_nda')
                            ->label('Es NDA')
                            ->helperText('Activar para convertir en Acuerdo de Confidencialidad.')
                            ->default(false)
                            ->inline(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $set('obligatoria', true);
                                }
                            }),
                        TextInput::make('vigencia_meses')
                            ->label('Vigencia')
                            ->numeric()
                            ->suffix('meses')
                            ->minValue(1)
                            ->maxValue(120)
                            ->visible(fn (Get $get) => $get('es_nda'))
                            ->required(fn (Get $get) => $get('es_nda'))
                            ->helperText('Meses de vigencia desde la firma.'),
                    ])->columns(4),

                Section::make('Contenido de la politica')
                    ->icon('heroicon-o-pencil-square')
                    ->description('Redacta el texto completo del NDA o politica. Este es el texto que los usuarios veran y deberan aceptar.')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('contenido')
                            ->label('Contenido')
                            ->required()
                            ->visible(fn (Get $get) => ! $get('es_nda'))
                            ->columnSpanFull(),
                        MarkdownEditor::make('contenido')
                            ->label('Contenido NDA (Markdown)')
                            ->required()
                            ->visible(fn (Get $get) => (bool) $get('es_nda'))
                            ->helperText('Variables disponibles: {nombre_completo}, {dni}, {direccion}, {telefono}, {puesto}. Ejemplo: "Yo, {nombre_completo}, identificado con DNI {dni}..."')
                            ->columnSpanFull(),
                    ]),

                Section::make('Documento oficial')
                    ->icon('heroicon-o-paper-clip')
                    ->description('Sube el documento Word o PDF oficial. Al subir un nuevo archivo se incrementara la version automaticamente.')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        FileUpload::make('archivo_path')
                            ->label('Documento (Word/PDF)')
                            ->disk('local')
                            ->directory('politicas/archivos')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->maxSize(10240)
                            ->storeFileNamesIn('archivo_nombre')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get, string $operation) {
                                if ($state && $operation === 'edit') {
                                    $current = $get('version');
                                    $parts = explode('.', $current);
                                    $major = (int) ($parts[0] ?? 1);
                                    $set('version', ($major + 1) . '.0');
                                }
                            })
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
            ->filters([])
            ->recordActions([
                Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('politicas.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('ver_firmantes')
                    ->label('Firmantes')
                    ->icon('heroicon-o-users')
                    ->color('warning')
                    ->url(fn ($record) => static::getUrl('firmantes', ['record' => $record])),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VersionesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPoliticas::route('/'),
            'create' => CreatePolitica::route('/create'),
            'edit' => EditPolitica::route('/{record}/edit'),
            'firmantes' => ViewNdaFirmantes::route('/{record}/firmantes'),
        ];
    }
}
