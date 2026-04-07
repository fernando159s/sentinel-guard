<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use App\Models\AceptacionPolitica;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewNdaFirmantes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PoliticaResource::class;

    protected static ?string $title = 'Firmantes NDA';

    protected string $view = 'filament.resources.politicas.pages.view-nda-firmantes';

    public $record;

    public function mount(int|string $record): void
    {
        $this->record = \App\Models\Politica::findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Firmantes — ' . $this->record->titulo;
    }

    public function getBreadcrumb(): string
    {
        return 'Firmantes';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AceptacionPolitica::query()
                    ->where('politica_id', $this->record->id)
                    ->with('user')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.dni')
                    ->label('DNI'),
                TextColumn::make('user.puesto')
                    ->label('Puesto'),
                TextColumn::make('version_aceptada')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                TextColumn::make('fecha_aceptacion')
                    ->label('Fecha firma')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('fecha_expiracion')
                    ->label('Expira')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin vigencia'),
                IconColumn::make('vigente')
                    ->label('Vigente')
                    ->state(fn (AceptacionPolitica $record) => $record->estaVigente())
                    ->boolean(),
            ])
            ->defaultSort('fecha_aceptacion', 'desc')
            ->recordActions([
                \Filament\Actions\Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (AceptacionPolitica $record) => route('politicas.nda-firmante-pdf', [
                        'politica' => $this->record,
                        'aceptacion' => $record,
                    ]))
                    ->openUrlInNewTab(),
            ]);
    }
}
