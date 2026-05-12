<?php

namespace App\Filament\Pages;

use App\Models\Capacitacion;
use App\Models\CapacitacionAsistencia;
use App\Models\User;
use App\Support\PdfBranding;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteCapacitaciones extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Reporte Capacitaciones';

    protected static ?string $title = 'Reporte de Capacitaciones';

    protected string $view = 'filament.pages.reporte-capacitaciones';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([])
            ->statePath('data');
    }

    public function generateReport(): StreamedResponse
    {
        $tenant = Filament::getTenant();

        $capacitaciones = Capacitacion::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->with(['asistencias', 'asistencias.user'])
            ->orderBy('fecha')
            ->get();

        $users = User::where('empresa_id', $tenant->id)
            ->where('estado', 'activo')
            ->orderBy('name')
            ->get();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 14,
            'margin_right' => 14,
            'tempDir' => storage_path('app/temp'),
        ]);

        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);

        $this->buildReportPages($mpdf, $tenant, $capacitaciones, $users, $hasLogo);

        $filename = 'reporte_capacitaciones_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildReportPages(Mpdf $mpdf, $tenant, $capacitaciones, $users, bool $hasLogo = false): void
    {
        $logoHtml = PdfBranding::logoHtml($hasLogo);
        $style = PdfBranding::reportStyle($tenant).'
        <style>
            .cap-header { background: #f0f0ff; padding: 6px 8px; border: 1px solid #e5e7eb; margin-bottom: 6px; border-radius: 3px; }
        </style>';

        // ═══ PAGE 1: Resumen general ═══
        $totalCap = $capacitaciones->count();
        $presenciales = $capacitaciones->where('modalidad.value', 'presencial')->count();
        $virtuales = $capacitaciones->where('modalidad.value', 'virtual')->count();
        $avgAsistencia = $totalCap > 0
            ? round($capacitaciones->avg(fn ($c) => $c->porcentajeAsistencia()), 1)
            : 0;

        $capRows = '';
        foreach ($capacitaciones as $c) {
            $total = $c->asistencias->count();
            $asistieron = $c->asistencias->where('asistio', true)->count();
            $pct = $total > 0 ? round(($asistieron / $total) * 100) : 0;
            $capRows .= '<tr>
                <td>'.e($c->tema).'</td>
                <td>'.$c->fecha->format('d/m/Y').'</td>
                <td>'.substr($c->hora_inicio, 0, 5).'</td>
                <td>'.$c->duracion_minutos.' min</td>
                <td>'.e($c->modalidad->label()).'</td>
                <td>'.e($c->expositor).'</td>
                <td>'.$asistieron.'/'.$total.'</td>
                <td>'.$pct.'%</td>
            </tr>';
        }

        $userRows = '';
        foreach ($users as $u) {
            $asistidas = CapacitacionAsistencia::where('user_id', $u->id)
                ->where('asistio', true)
                ->whereIn('capacitacion_id', $capacitaciones->pluck('id'))
                ->count();
            $pctUser = $totalCap > 0 ? round(($asistidas / $totalCap) * 100) : 0;
            $userRows .= '<tr>
                <td>'.e($u->name).'</td>
                <td>'.e($u->puesto ?? '—').'</td>
                <td>'.e($u->dni ?? '—').'</td>
                <td>'.$asistidas.'/'.$totalCap.'</td>
                <td>'.$pctUser.'%</td>
            </tr>';
        }

        $mpdf->WriteHTML($style."
            {$logoHtml}
            <h1>".e($tenant->razon_social)."</h1>
            <p style='color:#666;'>RUC: ".e($tenant->ruc)."</p>
            <p><strong>Reporte completo de Capacitaciones de Ciberseguridad</strong></p>

            <div class='summary'>
                <strong>Resumen general:</strong>
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

            <p class='footer'>
                Generado: ".now()->format('d/m/Y H:i').' | Admin: '.e(auth()->user()->name).' | SecuriForm
            </p>');

        // ═══ PAGES 2+: Una hoja por capacitación con sus asistentes ═══
        foreach ($capacitaciones as $c) {
            $mpdf->AddPage();

            $total = $c->asistencias->count();
            $asistieron = $c->asistencias->where('asistio', true)->count();
            $pct = $total > 0 ? round(($asistieron / $total) * 100) : 0;

            $asistenciaRows = '';
            foreach ($c->asistencias->sortBy('user.name') as $a) {
                $estado = $a->asistio ? 'Si' : 'No';
                $estadoColor = $a->asistio ? '#10b981' : '#ef4444';
                $confirmador = $a->confirmador?->name ?? '—';
                $fechaConf = $a->fecha_confirmacion?->format('d/m/Y H:i') ?? '—';

                $firmaCell = '<span style="color:#9ca3af;">—</span>';
                if ($a->asistio) {
                    $firma = $a->user?->firma_guardada;
                    if ($firma && str_starts_with($firma, 'data:image')) {
                        $firmaCell = '<img src="'.$firma.'" style="max-height:38px;max-width:120px;" />';
                    } else {
                        $firmaCell = '<span style="color:#dc2626;font-size:9px;">Sin firma</span>';
                    }
                }

                $asistenciaRows .= '<tr>
                    <td>'.e($a->user?->name ?? '—').'</td>
                    <td>'.e($a->user?->dni ?? '—').'</td>
                    <td>'.e($a->user?->puesto ?? '—').'</td>
                    <td style="color:'.$estadoColor.'; font-weight:bold;">'.$estado.'</td>
                    <td>'.e($confirmador).'</td>
                    <td>'.$fechaConf.'</td>
                    <td style="text-align:center;">'.$firmaCell.'</td>
                </tr>';
            }

            $mpdf->WriteHTML($style."
                {$logoHtml}
                <h1>".e($tenant->razon_social)."</h1>
                <p style='color:#666;'>RUC: ".e($tenant->ruc)."</p>

                <div class='cap-header'>
                    <h2 style='margin:0 0 6px;'>".e($c->tema)."</h2>
                    <table style='border:none; margin:0;'>
                        <tr style='border:none;'>
                            <td style='border:none; padding:2px 20px 2px 0;'><strong>Fecha:</strong> ".$c->fecha->format('d/m/Y')."</td>
                            <td style='border:none; padding:2px 20px 2px 0;'><strong>Hora:</strong> ".substr($c->hora_inicio, 0, 5).' ('.$c->duracion_minutos." min)</td>
                            <td style='border:none; padding:2px 20px 2px 0;'><strong>Modalidad:</strong> ".e($c->modalidad->label())."</td>
                        </tr>
                        <tr style='border:none;'>
                            <td style='border:none; padding:2px 20px 2px 0;'><strong>Expositor:</strong> ".e($c->expositor)."</td>
                            <td style='border:none; padding:2px 20px 2px 0;'><strong>Asistencia:</strong> {$asistieron}/{$total} ({$pct}%)</td>
                            <td style='border:none;'></td>
                        </tr>
                    </table>
                </div>

                <h3>Lista de asistencia</h3>
                <table>
                    <tr><th>Nombre</th><th>DNI</th><th>Puesto</th><th>Asistio</th><th>Confirmado por</th><th>Fecha confirmacion</th><th style="width:130px;">Firma</th></tr>
                    {$asistenciaRows}
                </table>

                <p class='footer'>
                    Generado: ".now()->format('d/m/Y H:i').' | Admin: '.e(auth()->user()->name).' | SecuriForm
                </p>');
        }
    }
}
