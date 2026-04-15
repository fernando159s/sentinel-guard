<?php

namespace App\Filament\Resources\Empresas;

use App\Filament\Resources\Empresas\Pages\CreateEmpresa;
use App\Filament\Resources\Empresas\Pages\EditEmpresa;
use App\Filament\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Resources\Empresas\Pages\ViewEmpresa;
use App\Filament\Resources\Empresas\Schemas\EmpresaForm;
use App\Filament\Resources\Empresas\Tables\EmpresasTable;
use App\Models\Empresa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return EmpresaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmpresasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\RegistrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmpresas::route('/'),
            'create' => CreateEmpresa::route('/create'),
            'view' => ViewEmpresa::route('/{record}'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }
}
