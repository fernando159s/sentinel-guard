<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nuevo ticket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero_ticket')
                            ->label('Número de ticket')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Se genera automáticamente'),
                        TextInput::make('asunto')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(300)
                            ->columnSpanFull(),
                        Select::make('categoria')
                            ->label('Categoría')
                            ->options([
                                'consulta' => 'Consulta',
                                'problema_tecnico' => 'Problema técnico',
                                'error_registro' => 'Error en registro',
                                'solicitud_acceso' => 'Solicitud de acceso',
                                'otro' => 'Otro',
                            ])
                            ->default('consulta')
                            ->required(),
                        Select::make('prioridad')
                            ->label('Prioridad')
                            ->options([
                                'baja' => 'Baja',
                                'media' => 'Media',
                                'alta' => 'Alta',
                                'urgente' => 'Urgente',
                            ])
                            ->default('media')
                            ->required(),
                        Select::make('equipos')
                            ->label('Equipo afectado')
                            ->multiple()
                            ->options(function () {
                                $empresa = Filament::getTenant();

                                return Equipo::where('empresa_id', $empresa?->id)
                                    ->where('estado', 'activo')
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => "{$e->codigo_interno} — {$e->marca} {$e->modelo}"])
                                    ->toArray();
                            })
                            ->searchable()
                            ->helperText('Opcional: selecciona los equipos relacionados con el problema')
                            ->columnSpanFull(),
                        RichEditor::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
