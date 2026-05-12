<?php

namespace App\Filament\Resources\Politicas\Pages;

use App\Filament\Resources\Politicas\PoliticaResource;
use App\Models\AceptacionPolitica;
use App\Models\Politica;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
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
        $this->record = Politica::findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Firmantes — '.$this->record->titulo;
    }

    public function getBreadcrumb(): string
    {
        return 'Firmantes';
    }

    protected function getHeaderActions(): array
    {
        $hasFirmados = AceptacionPolitica::where('politica_id', $this->record->id)
            ->whereNotNull('firma_imagen')
            ->whereHas('user', fn ($q) => $q->firmantes())
            ->exists();

        return [
            Action::make('descargar_resumen')
                ->label('Resumen firmantes (PDF)')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => route('politicas.resumen-firmantes-pdf', ['politica' => $this->record]))
                ->openUrlInNewTab(),

            Action::make('descargar_firmados_zip')
                ->label('Descargar todos los firmados (ZIP)')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('primary')
                ->url(fn () => route('politicas.firmados-zip', ['politica' => $this->record]))
                ->visible(fn () => $hasFirmados),
        ];
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
