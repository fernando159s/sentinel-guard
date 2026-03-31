<?php

namespace App\Filament\Resources\Registros;

use App\Filament\Resources\Registros\Pages\CreateRegistro;
use App\Filament\Resources\Registros\Pages\EditRegistro;
use App\Filament\Resources\Registros\Pages\ListRegistros;
use App\Filament\Resources\Registros\Schemas\RegistroForm;
use App\Filament\Resources\Registros\Tables\RegistrosTable;
use App\Models\Registro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistroResource extends Resource
{
    protected static ?string $model = Registro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?string $modelLabel = 'Registro';

    protected static ?string $pluralModelLabel = 'Registros';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RegistroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistros::route('/'),
            'create' => CreateRegistro::route('/create'),
            'edit' => EditRegistro::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
