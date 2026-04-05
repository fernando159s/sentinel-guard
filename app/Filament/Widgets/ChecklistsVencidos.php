<?php

namespace App\Filament\Widgets;

use App\Models\ChecklistPlantilla;
use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ChecklistsVencidos extends BaseWidget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Equipos con checklists vencidos';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function table(Table $table): Table
    {
        $empresa = Filament::getTenant();
        $empresaId = $empresa?->id;

        return $table
            ->query(
                Equipo::query()
                    ->where('empresa_id', $empresaId)
                    ->where('estado', 'activo')
                    ->whereDoesntHave('checklistEjecuciones', function (Builder $q) use ($empresaId) {
                        $plantillas = ChecklistPlantilla::where('empresa_id', $empresaId)
                            ->where('activa', true)
                            ->get();

                        // Has at least one recent execution within its periodicidad
                        $q->where(function ($sub) use ($plantillas) {
                            foreach ($plantillas as $p) {
                                $sub->orWhere(function ($w) use ($p) {
                                    $w->where('checklist_plantilla_id', $p->id)
                                      ->where('fecha_ejecucion', '>=', now()->subDays($p->diasPeriodicidad()));
                                });
                            }
                        });
                    })
            )
            ->columns([
                TextColumn::make('codigo_interno')
                    ->label('Codigo'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pc_escritorio' => 'PC', 'laptop' => 'Laptop',
                        'servidor' => 'Servidor', default => ucfirst($state),
                    }),
                TextColumn::make('marca')
                    ->label('Equipo')
                    ->formatStateUsing(fn ($record): string => trim(($record->marca ?? '') . ' ' . ($record->modelo ?? '')) ?: '—'),
                TextColumn::make('asignacionVigente.user.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Sin equipos pendientes')
            ->emptyStateDescription('Todos los equipos tienen sus checklists al dia.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
