<?php

namespace App\Filament\Widgets;

use App\Enums\ModalidadPago;
use App\Models\ActivoDigital;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ActivosPorVencer extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Activos digitales por vencer';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function table(Table $table): Table
    {
        $empresaId = Filament::getTenant()?->id;

        return $table
            ->query(
                ActivoDigital::query()
                    ->where('empresa_id', $empresaId)
                    ->whereIn('modalidad_pago', [ModalidadPago::Mensual->value, ModalidadPago::Anual->value])
                    ->whereNotNull('fecha_vencimiento')
                    ->whereNot('estado', 'cancelado')
                    ->where(fn (Builder $q) => $q
                        ->whereDate('fecha_vencimiento', '<', now())
                        ->orWhereDate('fecha_vencimiento', '<=', now()->addDays(30)))
                    ->orderBy('fecha_vencimiento')
            )
            ->columns([
                TextColumn::make('codigo_interno')
                    ->label('Codigo'),
                TextColumn::make('nombre')
                    ->label('Cuenta')
                    ->limit(30),
                TextColumn::make('proveedor')
                    ->label('Proveedor')
                    ->placeholder('—'),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn ($record): string => $record->estaVencido() ? 'danger' : 'warning'),
                TextColumn::make('dias_restantes')
                    ->label('Estado')
                    ->badge()
                    ->state(fn ($record): string => $record->estaVencido()
                        ? 'Vencido'
                        : 'En ' . (int) ceil(now()->diffInDays($record->fecha_vencimiento, false)) . ' dias')
                    ->color(fn ($record): string => $record->estaVencido() ? 'danger' : 'warning'),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Sin vencimientos proximos')
            ->emptyStateDescription('Ninguna cuenta vence en los proximos 30 dias.');
    }
}
