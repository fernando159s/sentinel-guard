<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTicketUpdates extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Ultimas actualizaciones de tickets';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'agente_helpdesk']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::withoutGlobalScopes()
                    ->with(['empresa', 'creador', 'agente'])
                    ->latest('fecha_ultima_actividad')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('numero_ticket')
                    ->label('Ticket')
                    ->searchable(),
                TextColumn::make('asunto')
                    ->label('Asunto')
                    ->limit(40),
                TextColumn::make('empresa.razon_social')
                    ->label('Empresa')
                    ->limit(25),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nuevo' => 'info',
                        'en_revision' => 'warning',
                        'esperando_usuario' => 'gray',
                        'resuelto' => 'success',
                        'cerrado' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nuevo' => 'Nuevo',
                        'en_revision' => 'En revision',
                        'esperando_usuario' => 'Esperando',
                        'resuelto' => 'Resuelto',
                        'cerrado' => 'Cerrado',
                        default => $state,
                    }),
                TextColumn::make('agente.name')
                    ->label('Agente')
                    ->placeholder('Sin asignar'),
                TextColumn::make('fecha_ultima_actividad')
                    ->label('Ultima actividad')
                    ->since(),
            ])
            ->paginated(false);
    }
}
