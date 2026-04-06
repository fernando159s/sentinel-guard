<?php

namespace App\Filament\Widgets;

use App\Enums\TipoFormato;
use App\Models\Registro;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestRegistros extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Ultimos registros';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    public function table(Table $table): Table
    {
        $empresa = Filament::getTenant();

        return $table
            ->query(
                Registro::query()
                    ->where('empresa_id', $empresa?->id)
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('numero_registro')
                    ->label('N° Registro')
                    ->searchable(),
                TextColumn::make('tipo_formato')
                    ->label('Formato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => TipoFormato::tryFrom($state)?->label() ?? $state),
                TextColumn::make('creador.name')
                    ->label('Creado por'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
