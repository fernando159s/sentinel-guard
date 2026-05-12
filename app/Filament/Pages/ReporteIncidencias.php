<?php

namespace App\Filament\Pages;

use App\Models\Registro;
use App\Support\PdfBranding;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteIncidencias extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa', 'agente_helpdesk']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Reporte Incidencias';

    protected static ?string $title = 'Reporte de Incidencias';

    protected string $view = 'filament.pages.reporte-incidencias';

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
        $bytes = $this->pdfBytes($tenant);
        $filename = 'reporte_incidencias_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($bytes) {
            echo $bytes;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function pdfBytes($tenant): string
    {
        $incidencias = Registro::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->where('tipo_formato', 'F09')
            ->with('creador')
            ->orderBy('created_at', 'desc')
            ->get();

        $resoluciones = Registro::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->where('tipo_formato', 'F10')
            ->with('creador')
            ->orderBy('created_at', 'desc')
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

        $this->buildReportPages($mpdf, $tenant, $incidencias, $resoluciones, $hasLogo);

        return $mpdf->Output('', 'S');
    }

    private function buildReportPages(Mpdf $mpdf, $tenant, $incidencias, $resoluciones, bool $hasLogo = false): void
    {
        $logoHtml = PdfBranding::logoHtml($hasLogo);
        $style = PdfBranding::reportStyle($tenant);

        // ═══ PAGE 1: Resumen general ═══
        $incTable = '';
        foreach ($incidencias as $i) {
            $datos = $i->datos ?? [];
            $sev = $datos['severidad'] ?? '-';
            $sevClass = match ($sev) {
                'alta' => 'badge-alta', 'media' => 'badge-media', default => 'badge-baja'
            };
            $incTable .= '<tr>
                <td>'.e($i->numero_registro).'</td>
                <td>'.e($this->labelFor('tipo_incidencia', $datos['tipo_incidencia'] ?? '-')).'</td>
                <td><span class="badge '.$sevClass.'">'.ucfirst($sev).'</span></td>
                <td>'.e($datos['sistema_equipo'] ?? '-').'</td>
                <td>'.e($i->creador?->name ?? '-').'</td>
                <td>'.$i->created_at->format('d/m/Y').'</td>
            </tr>';
        }

        $resTable = '';
        foreach ($resoluciones as $r) {
            $datos = $r->datos ?? [];
            $clas = $datos['clasificacion'] ?? '-';
            $clasClass = match ($clas) {
                'alta' => 'badge-alta', 'media' => 'badge-media', default => 'badge-baja'
            };
            $resTable .= '<tr>
                <td>'.e($r->numero_registro).'</td>
                <td>'.e($datos['incidencia_ref'] ?? '-').'</td>
                <td><span class="badge '.$clasClass.'">'.ucfirst($clas).'</span></td>
                <td>'.e($r->creador?->name ?? '-').'</td>
                <td>'.$r->created_at->format('d/m/Y').'</td>
            </tr>';
        }

        $tasaRes = $incidencias->count() > 0 ? round($resoluciones->count() / $incidencias->count() * 100) : 0;

        $mpdf->WriteHTML($style."
            {$logoHtml}
            <h1>".e($tenant->razon_social)."</h1>
            <p style='color:#666;'>RUC: ".e($tenant->ruc)."</p>
            <p><strong>Reporte completo de Incidencias de Seguridad</strong></p>

            <div class='summary'>
                <strong>Resumen:</strong>
                Incidencias (F09): {$incidencias->count()} |
                Resoluciones (F10): {$resoluciones->count()} |
                Tasa de resolucion: {$tasaRes}%
            </div>

            <h2>Incidencias registradas (F09) — {$incidencias->count()}</h2>
            <table>
                <tr><th>N°</th><th>Tipo</th><th>Severidad</th><th>Sistema/Equipo</th><th>Reporto</th><th>Fecha</th></tr>
                {$incTable}
            </table>

            <h2>Resoluciones registradas (F10) — {$resoluciones->count()}</h2>
            <table>
                <tr><th>N°</th><th>Incidencia Ref</th><th>Clasificacion</th><th>Ejecuto</th><th>Fecha</th></tr>
                {$resTable}
            </table>

            <p class='footer'>
                Generado: ".now()->format('d/m/Y H:i').' | Admin: '.e(auth()->user()->name).' | SecuriForm — PSC000001 / PSC000-25
            </p>');

        // ═══ PAGES 2+: Ficha detallada por cada incidencia (F09) ═══
        foreach ($incidencias as $i) {
            $mpdf->AddPage();
            $datos = $i->datos ?? [];
            $sev = $datos['severidad'] ?? '-';
            $sevClass = match ($sev) {
                'alta' => 'badge-alta', 'media' => 'badge-media', default => 'badge-baja'
            };

            $mpdf->WriteHTML($style."
                {$logoHtml}
                <h1>".e($tenant->razon_social)."</h1>
                <p style='color:#666;'>RUC: ".e($tenant->ruc)."</p>

                <div class='ficha'>
                    <div class='ficha-header'>
                        <h2 style='margin:0;'>F09 — Notificacion de Incidencia</h2>
                        <p style='margin:4px 0 0; color:#666;'>N° ".e($i->numero_registro)." | Politica: PSC000001 / PSC000-25</p>
                    </div>
                    <div class='ficha-body'>
                        <div class='grid-3'>
                            <div class='field'>
                                <div class='field-label'>Fecha / Hora del evento</div>
                                <div class='field-value'>".e($datos['fecha_evento'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Tipo de incidencia</div>
                                <div class='field-value'>".e($this->labelFor('tipo_incidencia', $datos['tipo_incidencia'] ?? '-'))."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Severidad</div>
                                <div class='field-value'><span class='badge {$sevClass}'>".ucfirst($sev)."</span></div>
                            </div>
                        </div>
                        <div class='grid-2'>
                            <div class='field'>
                                <div class='field-label'>Sistema / Equipo / Lugar</div>
                                <div class='field-value'>".e($datos['sistema_equipo'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Banco de datos</div>
                                <div class='field-value'>".e($datos['banco_datos'] ?? '-')."</div>
                            </div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Personas notificadas</div>
                            <div class='field-value'>".e(is_array($datos['personas_notificadas'] ?? null) ? implode(', ', $datos['personas_notificadas']) : ($datos['personas_notificadas'] ?? '-'))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Descripcion</div>
                            <div class='field-value-long'>".nl2br(e(strip_tags($datos['descripcion'] ?? '-')))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Medidas inmediatas</div>
                            <div class='field-value-long'>".nl2br(e($datos['medidas_inmediatas'] ?? '-'))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Impacto potencial</div>
                            <div class='field-value-long'>".nl2br(e($datos['impacto_potencial'] ?? '-'))."</div>
                        </div>
                        <div class='grid-2'>
                            <div class='field'>
                                <div class='field-label'>Comunica (nombre y cargo)</div>
                                <div class='field-value'>".e($datos['comunica_nombre'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Registrado por</div>
                                <div class='field-value'>".e($i->creador?->name ?? '-').' | '.$i->created_at->format('d/m/Y H:i')."</div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class='footer'>
                    Generado: ".now()->format('d/m/Y H:i').' | SecuriForm
                </p>');
        }

        // ═══ PAGES: Ficha detallada por cada resolucion (F10) ═══
        foreach ($resoluciones as $r) {
            $mpdf->AddPage();
            $datos = $r->datos ?? [];
            $clas = $datos['clasificacion'] ?? '-';
            $clasClass = match ($clas) {
                'alta' => 'badge-alta', 'media' => 'badge-media', default => 'badge-baja'
            };

            $mpdf->WriteHTML($style."
                {$logoHtml}
                <h1>".e($tenant->razon_social)."</h1>
                <p style='color:#666;'>RUC: ".e($tenant->ruc)."</p>

                <div class='ficha'>
                    <div class='ficha-header'>
                        <h2 style='margin:0;'>F10 — Resolucion de Incidencia</h2>
                        <p style='margin:4px 0 0; color:#666;'>N° ".e($r->numero_registro)." | Politica: PSC000-25</p>
                    </div>
                    <div class='ficha-body'>
                        <div class='grid-3'>
                            <div class='field'>
                                <div class='field-label'>N° Incidencia (referencia F09)</div>
                                <div class='field-value'>".e($datos['incidencia_ref'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Fecha / Hora de cierre</div>
                                <div class='field-value'>".e($datos['fecha_cierre'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Clasificacion</div>
                                <div class='field-value'><span class='badge {$clasClass}'>".ucfirst($clas)."</span></div>
                            </div>
                        </div>
                        <div class='grid-2'>
                            <div class='field'>
                                <div class='field-label'>Ejecuto (nombre y cargo)</div>
                                <div class='field-value'>".e($datos['ejecuto'] ?? '-')."</div>
                            </div>
                            <div class='field'>
                                <div class='field-label'>Firma responsable de seguridad</div>
                                <div class='field-value'>".e($datos['firma_responsable'] ?? '-')."</div>
                            </div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Requirio recuperacion</div>
                            <div class='field-value'>".(($datos['requirio_recuperacion'] ?? false) ? 'Si' : 'No')."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Medidas adoptadas</div>
                            <div class='field-value-long'>".nl2br(e($datos['medidas_adoptadas'] ?? '-'))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Resultado / Verificacion</div>
                            <div class='field-value-long'>".nl2br(e($datos['resultado_verificacion'] ?? '-'))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Acciones preventivas</div>
                            <div class='field-value-long'>".nl2br(e($datos['acciones_preventivas'] ?? '-'))."</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>Registrado por</div>
                            <div class='field-value'>".e($r->creador?->name ?? '-').' | '.$r->created_at->format('d/m/Y H:i')."</div>
                        </div>
                    </div>
                </div>

                <p class='footer'>
                    Generado: ".now()->format('d/m/Y H:i').' | SecuriForm
                </p>');
        }
    }

    private function labelFor(string $field, string $value): string
    {
        $labels = [
            'tipo_incidencia' => [
                'acceso_no_autorizado' => 'Acceso no autorizado',
                'perdida_datos' => 'Perdida de datos',
                'fuga_informacion' => 'Fuga de informacion',
                'malware' => 'Malware/Virus',
                'fallo_sistema' => 'Fallo de sistema',
                'otro' => 'Otro',
            ],
        ];

        return $labels[$field][$value] ?? ucfirst(str_replace('_', ' ', $value));
    }
}
