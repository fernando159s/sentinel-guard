<?php

namespace App\Filament\Resources\Capacitaciones;

use App\Filament\Resources\Capacitaciones\Pages\CreateCapacitacion;
use App\Filament\Resources\Capacitaciones\Pages\EditCapacitacion;
use App\Filament\Resources\Capacitaciones\Pages\ListCapacitaciones;
use App\Filament\Resources\Capacitaciones\Pages\ViewCapacitacion;
use App\Filament\Resources\Capacitaciones\Schemas\CapacitacionForm;
use App\Filament\Resources\Capacitaciones\Tables\CapacitacionesTable;
use App\Models\Capacitacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CapacitacionResource extends Resource
{
    protected static ?string $model = Capacitacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Capacitaciones';

    protected static ?string $modelLabel = 'Capacitacion';

    protected static ?string $pluralModelLabel = 'Capacitaciones';

    protected static ?string $slug = 'capacitaciones';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CapacitacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapacitacionesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCapacitaciones::route('/'),
            'create' => CreateCapacitacion::route('/create'),
            'view' => ViewCapacitacion::route('/{record}'),
            'edit' => EditCapacitacion::route('/{record}/edit'),
        ];
    }
}
