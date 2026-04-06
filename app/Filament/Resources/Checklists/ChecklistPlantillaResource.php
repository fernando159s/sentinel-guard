<?php

namespace App\Filament\Resources\Checklists;

use App\Filament\Resources\Checklists\Pages\CreateChecklistPlantilla;
use App\Filament\Resources\Checklists\Pages\EditChecklistPlantilla;
use App\Filament\Resources\Checklists\Pages\ListChecklistPlantillas;
use App\Models\ChecklistPlantilla;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistPlantillaResource extends Resource
{
    protected static ?string $model = ChecklistPlantilla::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Activos';

    protected static ?string $modelLabel = 'Checklist';

    protected static ?string $pluralModelLabel = 'Checklists';

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
                Section::make('Informacion del checklist')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->description('Define el nombre, periodicidad y los items que se verificaran en cada equipo.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la plantilla')
                            ->required()
                            ->placeholder('Verificacion mensual de PC')
                            ->columnSpanFull(),
                        Textarea::make('descripcion')
                            ->label('Descripcion')
                            ->rows(2)
                            ->placeholder('Que se verifica y por que...')
                            ->columnSpanFull(),
                        Select::make('periodicidad')
                            ->label('Periodicidad')
                            ->options([
                                'semanal' => 'Semanal',
                                'mensual' => 'Mensual',
                                'trimestral' => 'Trimestral',
                                'semestral' => 'Semestral',
                                'anual' => 'Anual',
                                'unica' => 'Unica vez',
                            ])
                            ->default('mensual')
                            ->required(),
                        Toggle::make('activa')
                            ->label('Activa')
                            ->default(true),
                    ])->columns(2),

                Section::make('Items a verificar')
                    ->icon('heroicon-o-list-bullet')
                    ->description('Agrega los puntos que se deben revisar. Los items obligatorios determinan si el checklist pasa o falla.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Item')
                                    ->required()
                                    ->placeholder('Ej: Antivirus actualizado'),
                                TextInput::make('descripcion')
                                    ->label('Descripcion')
                                    ->placeholder('Que debe verificarse exactamente...'),
                                Toggle::make('obligatorio')
                                    ->label('Obligatorio')
                                    ->default(true),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar item')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('periodicidad')
                    ->label('Periodicidad')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('items')
                    ->label('Items')
                    ->formatStateUsing(fn ($state): string => count($state ?? []) . ' items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('ejecuciones_count')
                    ->label('Ejecuciones')
                    ->counts('ejecuciones')
                    ->badge()
                    ->color('success'),
                IconColumn::make('activa')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChecklistPlantillas::route('/'),
            'create' => CreateChecklistPlantilla::route('/create'),
            'edit' => EditChecklistPlantilla::route('/{record}/edit'),
        ];
    }
}
