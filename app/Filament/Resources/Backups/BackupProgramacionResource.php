<?php

namespace App\Filament\Resources\Backups;

use App\Filament\Resources\Backups\Pages\CreateBackupProgramacion;
use App\Filament\Resources\Backups\Pages\EditBackupProgramacion;
use App\Filament\Resources\Backups\Pages\ListBackupProgramaciones;
use App\Filament\Resources\Backups\Schemas\BackupProgramacionForm;
use App\Filament\Resources\Backups\Tables\BackupProgramacionesTable;
use App\Models\BackupProgramacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BackupProgramacionResource extends Resource
{
    protected static ?string $model = BackupProgramacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static string|\UnitEnum|null $navigationGroup = 'Activos';

    protected static ?string $modelLabel = 'Programacion de Backup';

    protected static ?string $pluralModelLabel = 'Programaciones de Backup';

    protected static ?string $slug = 'backup-programaciones';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return BackupProgramacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BackupProgramacionesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackupProgramaciones::route('/'),
            'create' => CreateBackupProgramacion::route('/create'),
            'edit' => EditBackupProgramacion::route('/{record}/edit'),
        ];
    }
}
