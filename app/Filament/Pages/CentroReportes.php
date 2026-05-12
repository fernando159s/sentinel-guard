<?php

namespace App\Filament\Pages;

use App\Models\AceptacionPolitica;
use App\Models\Capacitacion;
use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
use App\Models\Equipo;
use App\Models\EquipoAsignacion;
use App\Models\Politica;
use App\Models\Registro;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CentroReportes extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa', 'agente_helpdesk']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = -10;

    protected static ?string $navigationLabel = 'Centro de Reportes';

    protected static ?string $title = 'Centro de Reportes';

    protected string $view = 'filament.pages.centro-reportes';

    public function getReportes(): array
    {
        return [
            [
                'title' => 'Incidencias y Resoluciones',
                'desc' => 'F09 + F10 — Notificaciones y resoluciones de incidencias de seguridad.',
                'icon' => 'heroicon-o-shield-exclamation',
                'color' => '#dc2626',
                'url' => ReporteIncidencias::getUrl(),
                'roles' => ['super_admin', 'admin_empresa', 'agente_helpdesk'],
            ],
            [
                'title' => 'Capacitaciones',
                'desc' => 'Capacitaciones de ciberseguridad y cumplimiento por usuario.',
                'icon' => 'heroicon-o-academic-cap',
                'color' => '#7c3aed',
                'url' => ReporteCapacitaciones::getUrl(),
                'roles' => ['super_admin', 'admin_empresa'],
            ],
            [
                'title' => 'Inventario de Soportes (F07)',
                'desc' => 'Inventario completo de equipos y soportes de informacion.',
                'icon' => 'heroicon-o-archive-box',
                'color' => '#0369a1',
                'url' => ReporteInventarioSoportes::getUrl(),
                'roles' => ['super_admin', 'admin_empresa'],
            ],
            [
                'title' => 'Movimientos de Soportes (F08)',
                'desc' => 'Ingresos, asignaciones, transferencias, mantenimientos, bajas.',
                'icon' => 'heroicon-o-arrows-right-left',
                'color' => '#059669',
                'url' => ReporteMovimientosSoportes::getUrl(),
                'roles' => ['super_admin', 'admin_empresa'],
            ],
            [
                'title' => 'Destruccion de Activos (F13)',
                'desc' => 'Registro de destruccion de activos con metodo y autorizacion.',
                'icon' => 'heroicon-o-trash',
                'color' => '#d97706',
                'url' => ReporteDestruccionActivos::getUrl(),
                'roles' => ['super_admin', 'admin_empresa'],
            ],
            [
                'title' => 'Checklists de Equipos',
                'desc' => 'Plantillas y ejecuciones de verificacion sobre equipos.',
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => '#0891b2',
                'url' => ReporteChecklists::getUrl(),
                'roles' => ['super_admin', 'admin_empresa'],
            ],
        ];
    }

    public function getReportesVisibles(): array
    {
        $user = auth()->user();

        return array_values(array_filter($this->getReportes(), fn ($r) => $user?->hasRole($r['roles'])));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reporte_completo')
                ->label('Reporte completo (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->size('lg')
                ->action(fn () => $this->generateReporteCompleto()),
        ];
    }

    public function generateReporteCompleto(): StreamedResponse
    {
        $tenant = Filament::getTenant();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'tempDir' => storage_path('app/temp'),
        ]);

        if ($tenant->logo_path) {
            $logoPath = storage_path('app/'.$tenant->logo_path);
            if (file_exists($logoPath)) {
                $mpdf->imageVars['logo'] = file_get_contents($logoPath);
            }
        }

        $this->buildCompleto($mpdf, $tenant);

        $filename = 'reporte_completo_'.now()->format('Ymd_His').'.pdf';

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildCompleto(Mpdf $mpdf, $tenant): void
    {
        $logoHtml = '';
        if ($tenant->logo_path && file_exists(storage_path('app/'.$tenant->logo_path))) {
            $logoHtml = '<img src="var:logo" style="height:60px;margin-bottom:8px;" /><br>';
        }

        $style = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
            h1 { color: #4338ca; font-size: 18px; margin-bottom: 2px; }
            h2 { font-size: 14px; margin-top: 18px; color: #1e1b4b; border-bottom: 2px solid #4338ca; padding-bottom: 4px; }
            h3 { font-size: 12px; margin-top: 12px; color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 6px; }
            th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; font-size: 9px; }
            .cover { text-align: center; padding: 80px 20px 20px; }
            .cover h1 { font-size: 26px; }
            .cover .subtitle { font-size: 14px; color: #6b7280; margin-top: 16px; }
            .cover .meta { margin-top: 60px; font-size: 11px; color: #4b5563; }
            .stat-grid table { margin-top: 8px; }
            .stat-grid td { text-align: center; padding: 8px 6px; background: #f9fafb; border: 1px solid #e5e7eb; }
            .stat-number { font-size: 20px; font-weight: bold; color: #4338ca; }
            .stat-label { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
            .section { margin-top: 20px; }
            .footer { margin-top: 30px; font-size: 9px; color: #888; }
        </style>';

        // ═══ COVER PAGE ═══
        $mpdf->WriteHTML($style.'
            <div class="cover">
                '.$logoHtml.'
                <h1>'.e($tenant->razon_social).'</h1>
                <p style="color:#666;">RUC: '.e($tenant->ruc).'</p>
                <div class="subtitle">Reporte completo de seguridad de la informacion</div>
                <div class="meta">
                    Generado el '.now()->format('d/m/Y H:i').'<br>
                    Por: '.e(auth()->user()->name).'
                </div>
            </div>');

        // ═══ EXECUTIVE SUMMARY ═══
        $mpdf->AddPage();

        $countF09 = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F09')->count();
        $countF10 = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F10')->count();
        $countF13 = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F13')->count();
        $countCap = Capacitacion::withoutGlobalScopes()->where('empresa_id', $tenant->id)->count();
        $equipoIds = Equipo::withoutGlobalScopes()->where('empresa_id', $tenant->id)->pluck('id');
        $countEq = $equipoIds->count();
        $countMov = EquipoAsignacion::whereIn('equipo_id', $equipoIds)->count();
        $countCheckPlant = ChecklistPlantilla::withoutGlobalScopes()->where('empresa_id', $tenant->id)->count();
        $countCheckEjec = ChecklistEjecucion::whereIn('equipo_id', $equipoIds)->count();
        $countPolitica = Politica::withoutGlobalScopes()->where('empresa_id', $tenant->id)->count();
        $countFirmados = AceptacionPolitica::whereHas('politica', fn ($q) => $q->withoutGlobalScopes()->where('empresa_id', $tenant->id))
            ->whereNotNull('firma_imagen')
            ->count();

        $mpdf->WriteHTML($style.'
            <h1>Resumen ejecutivo</h1>
            <p style="color:#666;">Estado consolidado al '.now()->format('d/m/Y').'</p>

            <div class="stat-grid">
                <table>
                    <tr>
                        <td><div class="stat-number">'.$countF09.'</div><div class="stat-label">Incidencias F09</div></td>
                        <td><div class="stat-number">'.$countF10.'</div><div class="stat-label">Resoluciones F10</div></td>
                        <td><div class="stat-number">'.$countF13.'</div><div class="stat-label">Destrucciones F13</div></td>
                        <td><div class="stat-number">'.$countCap.'</div><div class="stat-label">Capacitaciones</div></td>
                    </tr>
                    <tr>
                        <td><div class="stat-number">'.$countEq.'</div><div class="stat-label">Equipos en inventario</div></td>
                        <td><div class="stat-number">'.$countMov.'</div><div class="stat-label">Movimientos F08</div></td>
                        <td><div class="stat-number">'.$countCheckPlant.'</div><div class="stat-label">Plantillas checklist</div></td>
                        <td><div class="stat-number">'.$countCheckEjec.'</div><div class="stat-label">Ejecuciones checklist</div></td>
                    </tr>
                    <tr>
                        <td colspan="2"><div class="stat-number">'.$countPolitica.'</div><div class="stat-label">Politicas / NDA</div></td>
                        <td colspan="2"><div class="stat-number">'.$countFirmados.'</div><div class="stat-label">Documentos firmados</div></td>
                    </tr>
                </table>
            </div>');

        // ═══ INCIDENCIAS ═══
        $incidencias = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F09')->with('creador')->orderBy('created_at', 'desc')->get();
        $resoluciones = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F10')->with('creador')->orderBy('created_at', 'desc')->get();

        $tasaRes = $incidencias->count() > 0 ? round($resoluciones->count() / $incidencias->count() * 100) : 0;

        $incRows = '';
        foreach ($incidencias as $i) {
            $datos = $i->datos ?? [];
            $incRows .= '<tr>
                <td>'.e($i->numero_registro).'</td>
                <td>'.e($datos['tipo_incidencia'] ?? '-').'</td>
                <td>'.e($datos['severidad'] ?? '-').'</td>
                <td>'.e($i->creador?->name ?? '-').'</td>
                <td>'.$i->created_at->format('d/m/Y').'</td>
            </tr>';
        }

        $resRows = '';
        foreach ($resoluciones as $r) {
            $datos = $r->datos ?? [];
            $resRows .= '<tr>
                <td>'.e($r->numero_registro).'</td>
                <td>'.e($datos['incidencia_ref'] ?? '-').'</td>
                <td>'.e($datos['clasificacion'] ?? '-').'</td>
                <td>'.e($r->creador?->name ?? '-').'</td>
                <td>'.$r->created_at->format('d/m/Y').'</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>1. Incidencias y resoluciones</h2>
            <p>Tasa de resolucion: <strong>'.$tasaRes.'%</strong></p>
            <h3>F09 — Incidencias ('.$incidencias->count().')</h3>
            <table>
                <tr><th>N°</th><th>Tipo</th><th>Severidad</th><th>Reporto</th><th>Fecha</th></tr>
                '.($incRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin registros</td></tr>').'
            </table>
            <h3>F10 — Resoluciones ('.$resoluciones->count().')</h3>
            <table>
                <tr><th>N°</th><th>Ref F09</th><th>Clasificacion</th><th>Ejecuto</th><th>Fecha</th></tr>
                '.($resRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin registros</td></tr>').'
            </table>');

        // ═══ CAPACITACIONES ═══
        $mpdf->AddPage();
        $capacitaciones = Capacitacion::withoutGlobalScopes()->where('empresa_id', $tenant->id)->with('asistencias')->orderBy('fecha', 'desc')->get();
        $capRows = '';
        foreach ($capacitaciones as $c) {
            $totalA = $c->asistencias->count();
            $asistieron = $c->asistencias->where('asistio', true)->count();
            $pct = $totalA > 0 ? round($asistieron / $totalA * 100) : 0;
            $capRows .= '<tr>
                <td>'.e($c->tema).'</td>
                <td>'.$c->fecha->format('d/m/Y').'</td>
                <td>'.e($c->modalidad?->label() ?? '-').'</td>
                <td>'.e($c->expositor).'</td>
                <td>'.$asistieron.'/'.$totalA.' ('.$pct.'%)</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>2. Capacitaciones de ciberseguridad</h2>
            <table>
                <tr><th>Tema</th><th>Fecha</th><th>Modalidad</th><th>Expositor</th><th>Asistencia</th></tr>
                '.($capRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin capacitaciones</td></tr>').'
            </table>');

        // ═══ INVENTARIO ═══
        $mpdf->AddPage();
        $equipos = Equipo::withoutGlobalScopes()->where('empresa_id', $tenant->id)->orderBy('codigo_interno')->get();
        $eqRows = '';
        foreach ($equipos as $eq) {
            $eqRows .= '<tr>
                <td>'.e($eq->codigo_interno ?? '-').'</td>
                <td>'.e($eq->tipo).'</td>
                <td>'.e(trim(($eq->marca ?? '').' '.($eq->modelo ?? '')) ?: '-').'</td>
                <td>'.e($eq->numero_serie ?? '-').'</td>
                <td>'.e($eq->ubicacion ?? '-').'</td>
                <td>'.e($eq->estado ?? '-').'</td>
                <td>'.e($eq->nivel_sensibilidad ?? '-').'</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>3. Inventario de soportes (F07)</h2>
            <table>
                <tr><th>Codigo</th><th>Tipo</th><th>Marca/Modelo</th><th>S/N</th><th>Ubicacion</th><th>Estado</th><th>Sensibilidad</th></tr>
                '.($eqRows ?: '<tr><td colspan="7" style="text-align:center;color:#999;">Sin equipos</td></tr>').'
            </table>');

        // ═══ MOVIMIENTOS ═══
        $mpdf->AddPage();
        $movimientos = EquipoAsignacion::whereIn('equipo_id', $equipoIds)->with(['equipo', 'user'])->orderBy('fecha_inicio', 'desc')->get();
        $movRows = '';
        foreach ($movimientos as $m) {
            $movRows .= '<tr>
                <td>'.$m->fecha_inicio->format('d/m/Y').'</td>
                <td>'.e($m->equipo?->codigo_interno ?? '-').'</td>
                <td>'.e($m->tipo).'</td>
                <td>'.e($m->user?->name ?? '-').'</td>
                <td>'.e(mb_substr($m->motivo ?? '-', 0, 50)).'</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>4. Movimientos de soportes (F08)</h2>
            <table>
                <tr><th>Fecha</th><th>Equipo</th><th>Tipo</th><th>Usuario</th><th>Motivo</th></tr>
                '.($movRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin movimientos</td></tr>').'
            </table>');

        // ═══ DESTRUCCION ═══
        $destrucciones = Registro::withoutGlobalScopes()->where('empresa_id', $tenant->id)->where('tipo_formato', 'F13')->with('creador')->orderBy('created_at', 'desc')->get();
        $destRows = '';
        foreach ($destrucciones as $d) {
            $datos = $d->datos ?? [];
            $destRows .= '<tr>
                <td>'.e($d->numero_registro).'</td>
                <td>'.e($datos['fecha_destruccion'] ?? '-').'</td>
                <td>'.e($datos['metodo'] ?? '-').'</td>
                <td>'.e($datos['responsable'] ?? '-').'</td>
                <td>'.$d->created_at->format('d/m/Y').'</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h3>Destruccion de activos (F13) — '.$destrucciones->count().'</h3>
            <table>
                <tr><th>N°</th><th>Fecha destruccion</th><th>Metodo</th><th>Responsable</th><th>Registrado</th></tr>
                '.($destRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin destrucciones</td></tr>').'
            </table>');

        // ═══ CHECKLISTS ═══
        $mpdf->AddPage();
        $ejecuciones = ChecklistEjecucion::whereIn('equipo_id', $equipoIds)->with(['plantilla', 'equipo', 'ejecutor'])->orderBy('fecha_ejecucion', 'desc')->get();
        $ejecRows = '';
        foreach ($ejecuciones as $e) {
            $cumple = $e->itemsCumplen();
            $totalI = $e->totalItems();
            $pct = $totalI > 0 ? round($cumple / $totalI * 100) : 0;
            $ejecRows .= '<tr>
                <td>'.$e->fecha_ejecucion->format('d/m/Y').'</td>
                <td>'.e($e->plantilla?->nombre ?? '-').'</td>
                <td>'.e($e->equipo?->codigo_interno ?? '-').'</td>
                <td>'.e($e->ejecutor?->name ?? '-').'</td>
                <td>'.$cumple.'/'.$totalI.' ('.$pct.'%)</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>5. Checklists de equipos</h2>
            <table>
                <tr><th>Fecha</th><th>Plantilla</th><th>Equipo</th><th>Ejecutor</th><th>Score</th></tr>
                '.($ejecRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin ejecuciones</td></tr>').'
            </table>');

        // ═══ POLITICAS / NDAs ═══
        $mpdf->AddPage();
        $politicas = Politica::withoutGlobalScopes()->where('empresa_id', $tenant->id)->withCount(['aceptaciones'])->get();
        $polRows = '';
        foreach ($politicas as $p) {
            $firmados = AceptacionPolitica::where('politica_id', $p->id)->whereNotNull('firma_imagen')->count();
            $polRows .= '<tr>
                <td>'.e($p->titulo).'</td>
                <td>'.e($p->version).'</td>
                <td>'.($p->es_nda ? 'NDA' : 'Politica').'</td>
                <td>'.($p->activa ? 'Activa' : 'Inactiva').'</td>
                <td>'.$firmados.'</td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            <h2>6. Politicas y NDAs</h2>
            <table>
                <tr><th>Titulo</th><th>Version</th><th>Tipo</th><th>Estado</th><th>Firmados</th></tr>
                '.($polRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin politicas</td></tr>').'
            </table>

            <p class="footer">
                Reporte completo generado: '.now()->format('d/m/Y H:i').' | '.e(auth()->user()->name).' | SecuriForm
            </p>');
    }
}
