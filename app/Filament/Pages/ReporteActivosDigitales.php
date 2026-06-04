<?php

namespace App\Filament\Pages;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Models\ActivoDigital;
use App\Support\ActivoDigitalPdf;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteActivosDigitales extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Reporte de Activos Digitales';

    protected string $view = 'filament.pages.reporte-activos-digitales';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tipo' => '',
            'estado' => '',
            'modalidad_pago' => '',
        ]);
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                Select::make('tipo')
                    ->label('Tipo')
                    ->options(['' => 'Todos'] + TipoActivoDigital::options()),
                Select::make('estado')
                    ->label('Estado')
                    ->options(['' => 'Todos'] + EstadoActivoDigital::options()),
                Select::make('modalidad_pago')
                    ->label('Modalidad de pago')
                    ->options(['' => 'Todas'] + ModalidadPago::options()),
            ])
            ->statePath('data');
    }

    private function activos()
    {
        $query = ActivoDigital::withoutGlobalScopes()
            ->where('empresa_id', Filament::getTenant()->id)
            ->with(['responsables']);

        foreach (['tipo', 'estado', 'modalidad_pago'] as $key) {
            if (! empty($this->data[$key])) {
                $query->where($key, $this->data[$key]);
            }
        }

        return $query->orderBy('codigo_interno')->get();
    }

    public function generateInventario(): ?StreamedResponse
    {
        $bytes = ActivoDigitalPdf::inventario(Filament::getTenant(), $this->activos());

        return $this->descargarPdf($bytes, 'inventario_activos_digitales');
    }

    public function generateVencimientos(): ?StreamedResponse
    {
        $bytes = ActivoDigitalPdf::vencimientos(Filament::getTenant(), $this->activos());

        return $this->descargarPdf($bytes, 'vencimientos_activos_digitales');
    }

    private function descargarPdf(?string $bytes, string $nombre): ?StreamedResponse
    {
        if ($bytes === null) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No se encontraron cuentas con los filtros seleccionados.')
                ->warning()
                ->send();

            return null;
        }

        return response()->streamDownload(
            fn () => print ($bytes),
            $nombre.'_'.now()->format('Ymd').'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
