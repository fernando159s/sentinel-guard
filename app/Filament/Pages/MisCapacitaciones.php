<?php

namespace App\Filament\Pages;

use App\Enums\ModalidadCapacitacion;
use App\Filament\Resources\Capacitaciones\CapacitacionResource;
use App\Models\Capacitacion;
use App\Models\CapacitacionAsistencia;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MisCapacitaciones extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Capacitaciones';

    protected static ?string $title = 'Mis Capacitaciones';

    protected static ?string $slug = 'mis-capacitaciones';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.mis-capacitaciones';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Capacitacion::query()
                    ->where('empresa_id', Filament::getTenant()?->id)
                    ->with(['asistencias' => fn ($q) => $q->where('user_id', auth()->id())])
            )
            ->columns([
                TextColumn::make('tema')
                    ->label('Tema')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hora_inicio')
                    ->label('Hora')
                    ->time('H:i'),
                TextColumn::make('modalidad')
                    ->label('Modalidad')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ModalidadCapacitacion ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ModalidadCapacitacion ? $state->color() : 'gray'),
                TextColumn::make('expositor')
                    ->label('Expositor')
                    ->toggleable(),
                TextColumn::make('mi_asistencia')
                    ->label('Mi asistencia')
                    ->getStateUsing(function (Capacitacion $record) {
                        $asistencia = $record->asistencias->first();

                        if ($asistencia && $asistencia->asistio) {
                            return 'Confirmada';
                        }
                        if (! $record->yaTermino()) {
                            return 'Pendiente';
                        }
                        if ($record->dentroVentanaConfirmacion()) {
                            return 'Por confirmar';
                        }

                        return 'No confirmada';
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Confirmada' => 'success',
                        'Pendiente' => 'gray',
                        'Por confirmar' => 'warning',
                        'No confirmada' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordUrl(fn (Capacitacion $record) => CapacitacionResource::getUrl('view', [
                'record' => $record,
            ]))
            ->defaultSort('fecha', 'desc');
    }
}
