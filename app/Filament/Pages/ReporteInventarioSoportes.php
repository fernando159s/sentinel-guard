<?php

namespace App\Filament\Pages;

use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;

class ReporteInventarioSoportes extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Inventario Soportes (F07)';

    protected static ?string $title = 'Formato 7 — Inventario de Soportes';

    protected string $view = 'filament.pages.reporte-inventario-soportes';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'categoria' => '',
            'clasificacion_soporte' => '',
            'estado' => 'activo',
        ]);
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                Select::make('categoria')
                    ->label('Categoria')
                    ->options([
                        '' => 'Todas',
                        'tecnologico' => 'Tecnologico',
                        'no_tecnologico' => 'No tecnologico',
                    ]),
                Select::make('clasificacion_soporte')
                    ->label('Clasificacion soporte')
                    ->options([
                        '' => 'Todas',
                        'hdd_interno' => 'HDD interno',
                        'hdd_externo' => 'HDD externo',
                        'usb' => 'USB',
                        'servidor' => 'Servidor',
                        'nube' => 'Nube',
                        'dvd' => 'DVD / CD',
                        'expediente_fisico' => 'Expediente fisico',
                        'otro' => 'Otro',
                    ]),
                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        '' => 'Todos',
                        'activo' => 'Activo',
                        'mantenimiento' => 'En mantenimiento',
                        'obsoleto' => 'Obsoleto',
                        'dado_de_baja' => 'Dado de baja',
                    ]),
            ])
            ->statePath('data');
    }

    public function generateReport(): \Symfony\Component\HttpFoundation\StreamedResponse|null
    {
        $tenant = Filament::getTenant();

        $query = Equipo::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id);

        if (! empty($this->data['categoria'])) {
            $query->where('categoria', $this->data['categoria']);
        }
        if (! empty($this->data['clasificacion_soporte'])) {
            $query->where('clasificacion_soporte', $this->data['clasificacion_soporte']);
        }
        if (! empty($this->data['estado'])) {
            $query->where('estado', $this->data['estado']);
        }

        $equipos = $query->orderBy('codigo_interno')->get();

        if ($equipos->isEmpty()) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No se encontraron activos con los filtros seleccionados.')
                ->warning()
                ->send();

            return null;
        }

        $mpdf = new Mpdf([
            'format' => 'A4-L',
            'tempDir' => storage_path('app/temp'),
        ]);

        if ($tenant->logo_path) {
            $logoPath = storage_path('app/' . $tenant->logo_path);
            if (file_exists($logoPath)) {
                $mpdf->imageVars['logo'] = file_get_contents($logoPath);
            }
        }

        $this->buildReport($mpdf, $tenant, $equipos);

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, 'inventario_soportes_F07_' . now()->format('Ymd') . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function buildReport(Mpdf $mpdf, $tenant, $equipos): void
    {
        $logoHtml = '';
        if ($tenant->logo_path && file_exists(storage_path('app/' . $tenant->logo_path))) {
            $logoHtml = '<img src="var:logo" style="height:50px;margin-bottom:8px;" /><br>';
        }

        $style = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
            h1 { color: #4338ca; font-size: 16px; margin-bottom: 2px; }
            h2 { font-size: 13px; margin-top: 15px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; font-size: 8px; }
            .summary { margin-top: 12px; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 10px; }
            .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
            .badge-tec { background: #dbeafe; color: #1d4ed8; }
            .badge-notec { background: #fef3c7; color: #d97706; }
            .footer { margin-top: 20px; font-size: 8px; color: #888; }
        </style>';

        $tecCount = $equipos->where('categoria', 'tecnologico')->count();
        $noTecCount = $equipos->where('categoria', 'no_tecnologico')->count();

        $rows = '';
        foreach ($equipos as $eq) {
            $catBadge = $eq->categoria === 'tecnologico'
                ? '<span class="badge badge-tec">Tec</span>'
                : '<span class="badge badge-notec">No tec</span>';

            $rows .= '<tr>'
                . '<td>' . e($eq->codigo_interno ?? '-') . '</td>'
                . '<td>' . e($this->tipoLabel($eq->tipo)) . '</td>'
                . '<td>' . $catBadge . '</td>'
                . '<td>' . e($this->clasificacionLabel($eq->clasificacion_soporte)) . '</td>'
                . '<td>' . e(trim(($eq->marca ?? '') . ' ' . ($eq->modelo ?? '')) ?: '-') . '</td>'
                . '<td>' . e($eq->numero_serie ?? '-') . '</td>'
                . '<td>' . e($eq->ubicacion ?? '-') . '</td>'
                . '<td style="max-width:120px;">' . e(mb_substr($eq->contenido_datos ?? '-', 0, 80)) . '</td>'
                . '<td>' . e(ucfirst($eq->nivel_sensibilidad ?? '-')) . '</td>'
                . '<td>' . e(ucfirst($eq->estado)) . '</td>'
                . '<td>' . ($eq->fecha_adquisicion?->format('d/m/Y') ?? '-') . '</td>'
                . '</tr>';
        }

        $mpdf->WriteHTML($style . '
            ' . $logoHtml . '
            <h1>' . e($tenant->razon_social) . '</h1>
            <p style="color:#666;">RUC: ' . e($tenant->ruc) . '</p>
            <h2>Formato 7 — Inventario de Soportes</h2>
            <p style="color:#666;">Politica: PSC000003 / PSC000004 | Generado: ' . now()->format('d/m/Y H:i') . '</p>

            <div class="summary">
                <strong>Resumen:</strong>
                Total: ' . $equipos->count() . ' activos |
                Tecnologicos: ' . $tecCount . ' |
                No tecnologicos: ' . $noTecCount . '
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th>Cat.</th>
                        <th>Clasif. soporte</th>
                        <th>Marca / Modelo</th>
                        <th>N° Serie</th>
                        <th>Ubicacion</th>
                        <th>Contenido datos</th>
                        <th>Sensibilidad</th>
                        <th>Estado</th>
                        <th>Adquisicion</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>

            <p class="footer">
                Generado por: ' . e(auth()->user()->name) . ' | SecuriForm — PSC000003 / PSC000004
            </p>');
    }

    private function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'pc_escritorio' => 'PC Escritorio',
            'laptop' => 'Laptop',
            'impresora' => 'Impresora',
            'servidor' => 'Servidor',
            'usb' => 'USB',
            'disco_externo' => 'Disco externo',
            'telefono' => 'Telefono',
            'tablet' => 'Tablet',
            'dispositivo_red' => 'Disp. red',
            'dvd_cd' => 'DVD/CD',
            'expediente_fisico' => 'Expediente',
            'soporte_nube' => 'Cloud',
            'otro' => 'Otro',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }

    private function clasificacionLabel(?string $clas): string
    {
        if (! $clas) {
            return '-';
        }

        return match ($clas) {
            'hdd_interno' => 'HDD interno',
            'hdd_externo' => 'HDD externo',
            'usb' => 'USB',
            'servidor' => 'Servidor',
            'nube' => 'Nube',
            'dvd' => 'DVD/CD',
            'expediente_fisico' => 'Expediente',
            'otro' => 'Otro',
            default => ucfirst(str_replace('_', ' ', $clas)),
        };
    }
}
