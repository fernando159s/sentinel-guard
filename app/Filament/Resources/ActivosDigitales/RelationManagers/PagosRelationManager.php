<?php

namespace App\Filament\Resources\ActivosDigitales\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    protected static ?string $title = 'Historial de pagos';

    /** ¿El usuario puede registrar/editar pagos? */
    protected static function puedeGestionar(): bool
    {
        $user = auth()->user();

        return $user?->hasRole(['super_admin', 'admin_empresa'])
            || $user?->can('registrar_pagos')
            || false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                DatePicker::make('fecha_pago')
                    ->label('Fecha de pago')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),
                TextInput::make('monto')
                    ->label('Monto')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.01')
                    ->required()
                    ->prefix(fn (Get $get): string => $get('moneda') ?: 'PEN'),
                Select::make('moneda')
                    ->label('Moneda')
                    ->options([
                        'PEN' => 'PEN — Soles',
                        'USD' => 'USD — Dolares',
                        'EUR' => 'EUR — Euros',
                    ])
                    ->default(fn (): string => $this->getOwnerRecord()->moneda ?? 'PEN')
                    ->required()
                    ->live(),
                TextInput::make('metodo')
                    ->label('Metodo de pago')
                    ->placeholder('Tarjeta Visa, transferencia, PayPal...')
                    ->maxLength(255),
                DatePicker::make('periodo_desde')
                    ->label('Periodo desde'),
                DatePicker::make('periodo_hasta')
                    ->label('Periodo hasta')
                    ->afterOrEqual('periodo_desde'),
                FileUpload::make('comprobante')
                    ->label('Comprobante')
                    ->disk('local')
                    ->directory('activos-digitales/comprobantes')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_pago')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state, $record): string => $record->moneda . ' ' . number_format((float) $state, 2))
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 2)),
                TextColumn::make('metodo')
                    ->label('Metodo')
                    ->placeholder('—'),
                TextColumn::make('periodo')
                    ->label('Periodo')
                    ->state(fn ($record): string => $record->periodo_desde
                        ? $record->periodo_desde->format('d/m/Y') . ' → ' . ($record->periodo_hasta?->format('d/m/Y') ?? '...')
                        : '—'),
                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('fecha_pago', 'desc')
            ->recordActions([
                Action::make('comprobante')
                    ->label('Comprobante')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn ($record): bool => filled($record->comprobante))
                    ->action(fn ($record) => Storage::disk('local')->download($record->comprobante)),
                EditAction::make()
                    ->visible(fn (): bool => static::puedeGestionar()),
                DeleteAction::make()
                    ->visible(fn (): bool => static::puedeGestionar()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => static::puedeGestionar()),
            ])
            ->emptyStateHeading('Sin pagos registrados')
            ->emptyStateDescription('Registra aqui cada pago de la cuenta para llevar el historial y el costo acumulado.');
    }
}
