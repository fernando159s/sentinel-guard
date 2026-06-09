<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

/**
 * Auditoria del Baul: registro de "quien revelo o copio que credencial".
 *
 * Solo para roles de gestion (super_admin, admin_empresa). Lee del log
 * inmutable audit_logs las acciones 'credencial_revelada' y 'credencial_copiada',
 * acotadas a la empresa (tenant) actual.
 */
class AuditoriaBaul extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Activos Digitales';

    protected static ?string $title = 'Auditoría del Baúl';

    protected static ?string $navigationLabel = 'Auditoría del Baúl';

    protected static ?string $slug = 'baul-contrasenas/auditoria';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.auditoria-baul';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->whereIn('accion', ['credencial_revelada', 'credencial_copiada'])
                    ->where('empresa_id', Filament::getTenant()?->id)
                    ->with('usuario')
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('usuario')
                    ->label('Usuario')
                    // Se usa el nombre denormalizado del log ('por'): es estable aunque el
                    // actor sea de otra empresa (super_admin) o se elimine despues. La
                    // relacion ->usuario queda como respaldo para registros antiguos.
                    ->getStateUsing(fn (AuditLog $record): string => $record->datos_nuevos['por']
                        ?? $record->usuario?->name
                        ?? '— (usuario eliminado)'),
                TextColumn::make('accion')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'credencial_revelada' => 'warning',
                        'credencial_copiada' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'credencial_revelada' => 'heroicon-o-eye',
                        'credencial_copiada' => 'heroicon-o-clipboard',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'credencial_revelada' => 'Reveló',
                        'credencial_copiada' => 'Copió',
                        default => $state,
                    }),
                TextColumn::make('cuenta')
                    ->label('Cuenta')
                    ->getStateUsing(fn (AuditLog $record): string => $record->datos_nuevos['activo'] ?? '—'),
                TextColumn::make('etiqueta')
                    ->label('Credencial')
                    ->getStateUsing(fn (AuditLog $record): string => $record->datos_nuevos['etiqueta'] ?? '—'),
                TextColumn::make('campo')
                    ->label('Campo')
                    ->badge()
                    ->getStateUsing(fn (AuditLog $record): ?string => $record->datos_nuevos['campo'] ?? null)
                    ->placeholder('—'),
                TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(),
                TextColumn::make('user_agent')
                    ->label('Navegador')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('accion')
                    ->label('Acción')
                    ->options([
                        'credencial_revelada' => 'Revelaciones',
                        'credencial_copiada' => 'Copias',
                    ]),
                SelectFilter::make('usuario_id')
                    ->label('Usuario')
                    ->searchable()
                    // Opciones construidas sin tenant-scope: incluye actores de otra
                    // empresa (super_admin) que de otro modo quedarian fuera del filtro.
                    ->options(fn (): array => User::query()
                        ->withoutGlobalScopes()
                        ->whereIn('id', AuditLog::query()
                            ->whereIn('accion', ['credencial_revelada', 'credencial_copiada'])
                            ->where('empresa_id', Filament::getTenant()?->id)
                            ->distinct()
                            ->pluck('usuario_id')
                            ->filter())
                        ->pluck('name', 'id')
                        ->all()),
                Filter::make('rango')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Sin accesos registrados')
            ->emptyStateDescription('Aquí se registra cada vez que alguien revela o copia una contraseña del Baúl.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList);
    }
}
