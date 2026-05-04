<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use App\Models\AceptacionPolitica;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewNdaFirmantes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PoliticaResource::class;

    protected static ?string $title = 'Firmantes';

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
                    ->whereHas('user', fn ($q) => $q->firmantes())
                    ->with('user')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->join('users', 'aceptaciones_politica.user_id', '=', 'users.id')
                            ->orderBy('users.name', $direction)
                            ->select('aceptaciones_politica.*');
                    }),
                TextColumn::make('user.dni')
                    ->label('DNI')
                    ->placeholder('-'),
                TextColumn::make('user.puesto')
                    ->label('Puesto')
                    ->placeholder('-'),
                TextColumn::make('version_aceptada')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                IconColumn::make('firma_imagen')
                    ->label('Firma')
                    ->icon(fn ($state) => $state ? Heroicon::CheckCircle : Heroicon::OutlinedXCircle)
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('fecha_aceptacion')
                    ->label('Fecha firma')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('fecha_expiracion')
                    ->label('Expira')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Permanente')
                    ->visible(fn () => $this->record->es_nda),
                IconColumn::make('vigente')
                    ->label('Vigente')
                    ->state(fn (AceptacionPolitica $record) => $record->estaVigente())
                    ->boolean()
                    ->visible(fn () => $this->record->es_nda),
            ])
            ->defaultSort('fecha_aceptacion', 'desc')
            ->recordActions([
                Action::make('descargar_pdf')
                    ->label('Descargar PDF')
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
