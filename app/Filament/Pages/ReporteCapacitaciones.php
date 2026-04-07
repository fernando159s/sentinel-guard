<?php

namespace App\Filament\Pages;

use App\Models\Capacitacion;
use App\Models\CapacitacionAsistencia;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;

class ReporteCapacitaciones extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Reporte Capacitaciones';

    protected static ?string $title = 'Reporte Mensual de Capacitaciones';

    protected string $view = 'filament.pages.reporte-capacitaciones';

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

        $capacitaciones = Capacitacion::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->whereMonth('fecha', $month)
            ->whereYear('fecha', $year)
            ->with(['asistencias', 'asistencias.user'])
            ->orderBy('fecha')
            ->get();

        $users = User::where('empresa_id', $tenant->id)
            ->where('estado', 'activo')
            ->orderBy('name')
            ->get();

        $monthName = now()->setMonth($month)->translatedFormat('F');

        $html = $this->buildReportHtml($tenant, $capacitaciones, $users, $monthName, $year);

        $mpdf = new Mpdf([
            'format' => 'A4',
            'tempDir' => storage_path('app/temp'),
        ]);

        if ($tenant->logo_path) {
            $logoPath = storage_path('app/' . $tenant->logo_path);
            if (file_exists($logoPath)) {
                $mpdf->imageVars['logo'] = file_get_contents($logoPath);
            }
        }

        $mpdf->WriteHTML($html);

        $filename = "reporte_capacitaciones_{$year}_{$month}.pdf";

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildReportHtml($tenant, $capacitaciones, $users, $monthName, $year): string
    {
        // Metrics
        $totalCap = $capacitaciones->count();
        $presenciales = $capacitaciones->where('modalidad.value', 'presencial')->count();
        $virtuales = $capacitaciones->where('modalidad.value', 'virtual')->count();
        $avgAsistencia = $totalCap > 0
            ? round($capacitaciones->avg(fn ($c) => $c->porcentajeAsistencia()), 1)
            : 0;

        // Table 1: Capacitaciones
        $capRows = '';
        foreach ($capacitaciones as $c) {
            $total = $c->asistencias->count();
            $asistieron = $c->asistencias->where('asistio', true)->count();
            $pct = $total > 0 ? round(($asistieron / $total) * 100) : 0;
            $capRows .= "<tr>
                <td>" . e($c->tema) . "</td>
                <td>{$c->fecha->format('d/m/Y')}</td>
                <td>{$c->hora_inicio}</td>
                <td>{$c->duracion_minutos} min</td>
                <td>" . e($c->modalidad->label()) . "</td>
                <td>" . e($c->expositor) . "</td>
                <td>{$asistieron}/{$total}</td>
                <td>{$pct}%</td>
            </tr>";
        }

        // Table 2: User compliance
        $userRows = '';
        foreach ($users as $u) {
            $asistidas = CapacitacionAsistencia::where('user_id', $u->id)
                ->where('asistio', true)
                ->whereIn('capacitacion_id', $capacitaciones->pluck('id'))
                ->count();
            $pctUser = $totalCap > 0 ? round(($asistidas / $totalCap) * 100) : 0;
            $userRows .= "<tr>
                <td>" . e($u->name) . "</td>
                <td>" . e($u->puesto ?? '—') . "</td>
                <td>" . e($u->dni ?? '—') . "</td>
                <td>{$asistidas}/{$totalCap}</td>
                <td>{$pctUser}%</td>
            </tr>";
        }

        $logoHtml = '';
        if ($tenant->logo_path && file_exists(storage_path('app/' . $tenant->logo_path))) {
            $logoHtml = '<img src="var:logo" style="height:50px;margin-bottom:8px;" /><br>';
        }

        return "
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            h1 { color: #4338ca; font-size: 16px; margin-bottom: 2px; }
            h2 { font-size: 13px; margin-top: 20px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; }
            .summary { margin-top: 15px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
            .metrics { display: flex; margin-top: 10px; }
            .metric { padding: 8px 12px; margin-right: 10px; background: #f0f0ff; border: 1px solid #ddd; text-align: center; }
        </style>

        {$logoHtml}
        <h1>" . e($tenant->razon_social) . "</h1>
        <p style='color:#666;'>RUC: " . e($tenant->ruc) . "</p>
        <p><strong>Reporte de Capacitaciones de Ciberseguridad — {$monthName} {$year}</strong></p>

        <div class='summary'>
            <strong>Resumen del periodo:</strong>
            Total capacitaciones: {$totalCap} |
            Presenciales: {$presenciales} |
            Virtuales: {$virtuales} |
            Asistencia promedio: {$avgAsistencia}%
        </div>

        <h2>Capacitaciones realizadas</h2>
        <table>
            <tr><th>Tema</th><th>Fecha</th><th>Hora</th><th>Duracion</th><th>Modalidad</th><th>Expositor</th><th>Asistentes</th><th>%</th></tr>
            {$capRows}
        </table>

        <h2>Cumplimiento por usuario</h2>
        <table>
            <tr><th>Nombre</th><th>Puesto</th><th>DNI</th><th>Asistio</th><th>%</th></tr>
            {$userRows}
        </table>

        <p style='margin-top:30px;font-size:9px;color:#888;'>
            Generado: " . now()->format('d/m/Y H:i') . " | Admin: " . e(auth()->user()->name) . " | SecuriForm
        </p>";
    }
}
