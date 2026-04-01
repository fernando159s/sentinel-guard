<?php

namespace App\Filament\Pages;

use App\Enums\TipoFormato;
use App\Models\Registro;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;

class ReporteIncidencias extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Reporte Incidencias';

    protected static ?string $title = 'Reporte Mensual de Incidencias';

    protected string $view = 'filament.pages.reporte-incidencias';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'mes' => (string) now()->month,
            'anio' => (string) now()->year,
        ]);
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[(string) $m] = now()->setMonth($m)->translatedFormat('F');
        }

        $years = [];
        for ($y = now()->year; $y >= now()->year - 3; $y--) {
            $years[(string) $y] = (string) $y;
        }

        return $form
            ->schema([
                Select::make('mes')
                    ->label('Mes')
                    ->options($months)
                    ->required(),
                Select::make('anio')
                    ->label('Año')
                    ->options($years)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function generateReport(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $tenant = Filament::getTenant();
        $month = (int) $this->data['mes'];
        $year = (int) $this->data['anio'];

        $incidencias = Registro::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->where('tipo_formato', 'F09')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->with('creador')
            ->get();

        $resoluciones = Registro::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->where('tipo_formato', 'F10')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->with('creador')
            ->get();

        $monthName = now()->setMonth($month)->translatedFormat('F');

        $html = $this->buildReportHtml($tenant, $incidencias, $resoluciones, $monthName, $year);

        $mpdf = new Mpdf([
            'format' => 'A4',
            'tempDir' => storage_path('app/temp'),
        ]);
        $mpdf->WriteHTML($html);

        $filename = "reporte_incidencias_{$year}_{$month}.pdf";

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildReportHtml($tenant, $incidencias, $resoluciones, $monthName, $year): string
    {
        $incTable = '';
        foreach ($incidencias as $i) {
            $datos = $i->datos ?? [];
            $incTable .= "<tr>
                <td>{$i->numero_registro}</td>
                <td>" . ($datos['tipo_incidencia'] ?? '-') . "</td>
                <td>" . ($datos['severidad'] ?? '-') . "</td>
                <td>{$i->creador?->name}</td>
                <td>{$i->created_at->format('d/m/Y')}</td>
            </tr>";
        }

        $resTable = '';
        foreach ($resoluciones as $r) {
            $datos = $r->datos ?? [];
            $resTable .= "<tr>
                <td>{$r->numero_registro}</td>
                <td>" . ($datos['incidencia_ref'] ?? '-') . "</td>
                <td>" . ($datos['clasificacion'] ?? '-') . "</td>
                <td>{$r->created_at->format('d/m/Y')}</td>
            </tr>";
        }

        return "
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            h1 { color: #4338ca; font-size: 16px; }
            h2 { font-size: 13px; margin-top: 20px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; }
            .summary { margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
        </style>
        <h1>{$tenant->razon_social}</h1>
        <p>Reporte Mensual de Incidencias — {$monthName} {$year}</p>

        <h2>Incidencias (F09) — {$incidencias->count()} registros</h2>
        <table>
            <tr><th>N°</th><th>Tipo</th><th>Severidad</th><th>Reportó</th><th>Fecha</th></tr>
            {$incTable}
        </table>

        <h2>Resoluciones (F10) — {$resoluciones->count()} registros</h2>
        <table>
            <tr><th>N°</th><th>Incidencia Ref</th><th>Clasificación</th><th>Fecha</th></tr>
            {$resTable}
        </table>

        <div class='summary'>
            <strong>Resumen:</strong> {$incidencias->count()} incidencias, {$resoluciones->count()} resoluciones.
            Tasa de resolución: " . ($incidencias->count() > 0 ? round($resoluciones->count() / $incidencias->count() * 100) : 0) . "%
        </div>

        <p style='margin-top:30px;font-size:9px;color:#888;'>
            Generado: " . now()->format('d/m/Y H:i') . " | Admin: " . auth()->user()->name . "
        </p>";
    }
}
