<?php

namespace App\Filament\Resources\ActivosDigitales;

use App\Filament\Resources\ActivosDigitales\Pages\CreateActivoDigital;
use App\Filament\Resources\ActivosDigitales\Pages\EditActivoDigital;
use App\Filament\Resources\ActivosDigitales\Pages\ListActivosDigitales;
use App\Filament\Resources\ActivosDigitales\RelationManagers;
use App\Filament\Resources\ActivosDigitales\Schemas\ActivoDigitalForm;
use App\Filament\Resources\ActivosDigitales\Tables\ActivosDigitalesTable;
use App\Models\ActivoDigital;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivoDigitalResource extends Resource
{
    protected static ?string $model = ActivoDigital::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Activos Digitales';

    protected static ?string $modelLabel = 'Activo digital';

    protected static ?string $pluralModelLabel = 'Activos digitales';

    protected static ?string $slug = 'cuentas-digitales';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Admins gestionan el modulo; un usuario no-admin entra si es
        // responsable de al menos una cuenta (vera solo las suyas).
        if ($user->hasRole(['super_admin', 'admin_empresa'])) {
            return true;
        }

        return ActivoDigital::whereHas('responsables', fn (Builder $q) => $q->whereKey($user->id))->exists();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Los responsables no-admin solo ven las cuentas a su cargo.
        if ($user && ! $user->hasRole(['super_admin', 'admin_empresa'])) {
            $query->whereHas('responsables', fn (Builder $q) => $q->whereKey($user->id));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return ActivoDigitalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivosDigitalesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CredencialesRelationManager::class,
            RelationManagers\PagosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivosDigitales::route('/'),
            'create' => CreateActivoDigital::route('/create'),
            'edit' => EditActivoDigital::route('/{record}/edit'),
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
