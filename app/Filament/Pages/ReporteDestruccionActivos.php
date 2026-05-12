<?php

namespace App\Filament\Pages;

use App\Models\Equipo;
use App\Models\Registro;
use App\Support\PdfBranding;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteDestruccionActivos extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Destruccion Activos (F13)';

    protected static ?string $title = 'Formato 13 — Destruccion de Activos';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reporte-destruccion-activos';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'metodo' => '',
            'motivo' => '',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('metodo')
                    ->label('Metodo de destruccion (opcional)')
                    ->options([
                        '' => 'Todos',
                        'borrado_seguro' => 'Borrado seguro',
                        'destruccion_fisica' => 'Destruccion fisica',
                        'desmagnetizacion' => 'Desmagnetizacion',
                        'incineracion' => 'Incineracion',
                        'trituracion' => 'Trituracion',
                        'proveedor_certificado' => 'Proveedor certificado',
                    ]),
                Select::make('motivo')
                    ->label('Motivo de baja (opcional)')
                    ->options([
                        '' => 'Todos',
                        'obsoleto' => 'Obsoleto',
                        'danado' => 'Danado',
                        'destruido' => 'Destruido',
                        'perdido' => 'Perdido',
                        'robado' => 'Robado',
                    ]),
            ])
            ->statePath('data');
    }

    public function generateReport(): ?StreamedResponse
    {
        $tenant = Filament::getTenant();
        $bytes = $this->pdfBytes($tenant);

        if ($bytes === null) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No se encontraron registros F13 con los filtros seleccionados.')
                ->warning()
                ->send();

            return null;
        }

        $filename = 'destruccion_activos_F13_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($bytes) {
            echo $bytes;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function pdfBytes($tenant, array $filtros = []): ?string
    {
        $query = Registro::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->where('tipo_formato', 'F13')
            ->with(['creador', 'equipo']);

        $metodo = $filtros['metodo'] ?? ($this->data['metodo'] ?? null);
        $motivo = $filtros['motivo'] ?? ($this->data['motivo'] ?? null);

        if (! empty($metodo)) {
            $query->where('datos->metodo', $metodo);
        }

        $registros = $query->orderBy('created_at', 'desc')->get();

        if (! empty($motivo)) {
            $registros = $registros->filter(function ($r) use ($motivo) {
                $obs = $r->datos['observaciones'] ?? '';

                return str_contains(mb_strtolower($obs), mb_strtolower($motivo));
            })->values();
        }

        if ($registros->isEmpty()) {
            return null;
        }

        $equipoIds = $registros->pluck('equipo_id')->filter()->unique()->toArray();
        $equipos = Equipo::withoutGlobalScopes()
            ->withTrashed()
            ->whereIn('id', $equipoIds)
            ->get()
            ->keyBy('id');

        $mpdf = new Mpdf([
            'format' => 'A4-L',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => storage_path('app/temp'),
        ]);

        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);

        $this->buildReport($mpdf, $tenant, $registros, $equipos, $hasLogo);

        return $mpdf->Output('', 'S');
    }

    private function buildReport(Mpdf $mpdf, $tenant, $registros, $equipos, bool $hasLogo = false): void
    {
        $logoHtml = PdfBranding::logoHtml($hasLogo, 32);
        $colors = PdfBranding::colors($tenant);
        $style = PdfBranding::reportStyle($tenant).'
        <style>
            body { font-size: 8px; }
            h1 { font-size: 12px; }
            h2 { font-size: 10px; margin-top: 7px; }
            h3 { font-size: 9px; margin-top: 5px; }
            th, td { padding: 2px 4px; }
            th { font-size: 7.5px; }
            .stat-grid { margin-top: 5px; }
            .stat-grid table { margin-top: 2px; }
            .stat-grid td { text-align: center; padding: 4px 6px; }
            .stat-number { font-size: 14px; font-weight: bold; color: '.$colors['primario'].'; }
            .stat-label { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
            .badge-danger { background: #fee2e2; color: #b91c1c; }
            .badge-warning { background: #fef3c7; color: #b45309; }
            .badge-info { background: #dbeafe; color: #1d4ed8; }
            .badge-success { background: #d1fae5; color: #047857; }
            .badge-purple { background: #ede9fe; color: #7c3aed; }
            .ficha-header { background: #fef2f2; padding: 5px 8px; }
            .field-value-long { font-size: 8px; padding: 3px 5px; }
            .equipo-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 3px; padding: 5px 7px; margin-top: 5px; font-size: 7.5px; }
            .equipo-box strong { color: #0369a1; }
        </style>';

        // ═══ Statistics ═══
        $total = $registros->count();
        $conEquipo = $registros->where('equipo_id', '!=', null)->count();
        $sinEquipo = $total - $conEquipo;

        // By method
        $porMetodo = $registros->groupBy(fn ($r) => $r->datos['metodo'] ?? 'sin_especificar')->map->count();

        // By month
        $porMes = $registros->groupBy(fn ($r) => $r->created_at->format('Y-m'))->map->count()->sortKeys();

        // Motivo breakdown (from observaciones)
        $motivos = ['obsoleto' => 0, 'danado' => 0, 'destruido' => 0, 'perdido' => 0, 'robado' => 0, 'otro' => 0];
        foreach ($registros as $r) {
            $obs = mb_strtolower($r->datos['observaciones'] ?? '');
            $found = false;
            foreach (['obsoleto', 'danado', 'destruido', 'perdido', 'robado'] as $m) {
                if (str_contains($obs, $m)) {
                    $motivos[$m]++;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $motivos['otro']++;
            }
        }

        // Equipment categories
        $tecCount = 0;
        $noTecCount = 0;
        foreach ($registros as $r) {
            if ($r->equipo_id && isset($equipos[$r->equipo_id])) {
                if ($equipos[$r->equipo_id]->categoria === 'tecnologico') {
                    $tecCount++;
                } else {
                    $noTecCount++;
                }
            }
        }

        // ═══ PAGE 1: Summary + Statistics + Table ═══
        $metodoRows = '';
        foreach ($porMetodo as $metodo => $count) {
            $pct = round($count / $total * 100);
            $metodoRows .= '<tr><td>'.e($this->metodoLabel($metodo)).'</td><td style="text-align:center;">'.$count.'</td><td style="text-align:center;">'.$pct.'%</td></tr>';
        }

        $mesRows = '';
        foreach ($porMes as $mes => $count) {
            $mesLabel = date('M Y', strtotime($mes.'-01'));
            $mesRows .= '<tr><td>'.$mesLabel.'</td><td style="text-align:center;">'.$count.'</td></tr>';
        }

        $motivoRows = '';
        foreach ($motivos as $motivo => $count) {
            if ($count > 0) {
                $motivoRows .= '<tr><td>'.e($this->motivoLabel($motivo)).'</td><td style="text-align:center;">'.$count.'</td></tr>';
            }
        }

        // Main table
        $rows = '';
        foreach ($registros as $r) {
            $datos = $r->datos ?? [];
            $eq = ($r->equipo_id && isset($equipos[$r->equipo_id])) ? $equipos[$r->equipo_id] : null;

            $metodoBadge = $this->metodoBadge($datos['metodo'] ?? null);

            $equipoInfo = '-';
            if ($eq) {
                $equipoInfo = e(trim("{$eq->tipo} {$eq->marca} {$eq->modelo}"))
                    .'<br><span style="font-size:7px;color:#666;">'
                    .e($eq->codigo_interno ?? '').' | S/N: '.e($eq->numero_serie ?? '-')
                    .'</span>';
            }

            $rows .= '<tr>'
                .'<td>'.e($r->numero_registro).'</td>'
                .'<td>'.e($datos['fecha_destruccion'] ?? '-').'</td>'
                .'<td>'.$metodoBadge.'</td>'
                .'<td style="max-width:150px;">'.$equipoInfo.'</td>'
                .'<td style="max-width:120px;">'.e(mb_substr($datos['descripcion_activo'] ?? '-', 0, 80)).'</td>'
                .'<td>'.e($datos['responsable'] ?? '-').'</td>'
                .'<td>'.e($datos['autoriza'] ?? '-').'</td>'
                .'<td>'.e($r->creador?->name ?? '-').'</td>'
                .'<td>'.$r->created_at->format('d/m/Y').'</td>'
                .'</tr>';
        }

        $mpdf->WriteHTML($style.'
            '.$logoHtml.'
            <h1>'.e($tenant->razon_social).' <span style="font-size:9px;color:#666;font-weight:normal;">RUC: '.e($tenant->ruc).'</span></h1>
            <h2>Formato 13 — Registro de Destruccion de Activos</h2>
            <p style="color:#666;font-size:8px;margin:0;">Reporte completo | Politica: PSC000003 / PSC000004 | Generado: '.now()->format('d/m/Y H:i').'</p>

            <div class="stat-grid">
                <table>
                    <tr>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">'.$total.'</div>
                            <div class="stat-label">Total destrucciones</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">'.$conEquipo.'</div>
                            <div class="stat-label">Con equipo vinculado</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">'.$tecCount.'</div>
                            <div class="stat-label">Activos tecnologicos</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">'.$noTecCount.'</div>
                            <div class="stat-label">Activos no tecnologicos</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">'.$sinEquipo.'</div>
                            <div class="stat-label">Sin equipo vinculado</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="display:flex;gap:0;margin-top:6px;">
                <div style="width:40%;">
                    <h3>Por metodo de destruccion</h3>
                    <table>
                        <tr><th>Metodo</th><th style="text-align:center;">Cantidad</th><th style="text-align:center;">%</th></tr>
                        '.$metodoRows.'
                    </table>
                </div>
                <div style="width:30%;padding-left:10px;">
                    <h3>Por motivo de baja</h3>
                    <table>
                        <tr><th>Motivo</th><th style="text-align:center;">Cant.</th></tr>
                        '.$motivoRows.'
                    </table>
                </div>
                <div style="width:30%;padding-left:10px;">
                    <h3>Tendencia mensual</h3>
                    <table>
                        <tr><th>Mes</th><th style="text-align:center;">Cant.</th></tr>
                        '.$mesRows.'
                    </table>
                </div>
            </div>

            <h2>Detalle de registros F13 — '.$total.' resultados</h2>
            <table>
                <thead>
                    <tr>
                        <th>N° Registro</th>
                        <th>Fecha destr.</th>
                        <th>Metodo</th>
                        <th>Equipo vinculado</th>
                        <th>Descripcion activo</th>
                        <th>Responsable</th>
                        <th>Autoriza</th>
                        <th>Creado por</th>
                        <th>Fecha reg.</th>
                    </tr>
                </thead>
                <tbody>'.$rows.'</tbody>
            </table>

            <p class="footer">
                Generado por: '.e(auth()->user()->name).' | SecuriForm — PSC000003 / PSC000004
            </p>');

        // ═══ PAGE 2+: Individual detail cards per F13 registro ═══
        foreach ($registros as $r) {
            $mpdf->AddPage('P'); // Portrait for detail cards
            $datos = $r->datos ?? [];
            $eq = ($r->equipo_id && isset($equipos[$r->equipo_id])) ? $equipos[$r->equipo_id] : null;
            $metodoBadge = $this->metodoBadge($datos['metodo'] ?? null);

            $equipoSection = '';
            if ($eq) {
                $asignacionVigente = $eq->asignaciones()
                    ->where('tipo', 'baja')
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();

                $equipoSection = '
                <div class="equipo-box">
                    <strong>Equipo vinculado: '.e($eq->codigo_interno).'</strong><br>
                    <div class="grid-3" style="margin-top:6px;">
                        <div class="field">
                            <div class="field-label">Tipo</div>
                            <div class="field-value">'.e($this->tipoLabel($eq->tipo)).'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Marca / Modelo</div>
                            <div class="field-value">'.e(trim("{$eq->marca} {$eq->modelo}") ?: '-').'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">N° Serie</div>
                            <div class="field-value">'.e($eq->numero_serie ?? '-').'</div>
                        </div>
                    </div>
                    <div class="grid-3" style="margin-top:4px;">
                        <div class="field">
                            <div class="field-label">Categoria</div>
                            <div class="field-value">'.($eq->categoria === 'tecnologico' ? '<span class="badge badge-info">Tecnologico</span>' : '<span class="badge badge-warning">No tecnologico</span>').'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Clasif. soporte</div>
                            <div class="field-value">'.e($this->clasificacionLabel($eq->clasificacion_soporte)).'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Sensibilidad</div>
                            <div class="field-value">'.e(ucfirst($eq->nivel_sensibilidad ?? '-')).'</div>
                        </div>
                    </div>
                    <div class="grid-3" style="margin-top:4px;">
                        <div class="field">
                            <div class="field-label">Ubicacion</div>
                            <div class="field-value">'.e($eq->ubicacion ?? '-').'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Fecha adquisicion</div>
                            <div class="field-value">'.($eq->fecha_adquisicion?->format('d/m/Y') ?? '-').'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Estado actual</div>
                            <div class="field-value"><span class="badge badge-danger">'.e(ucfirst(str_replace('_', ' ', $eq->estado))).'</span></div>
                        </div>
                    </div>'
                    .($eq->contenido_datos ? '
                    <div class="field" style="margin-top:4px;">
                        <div class="field-label">Contenido / Datos almacenados</div>
                        <div class="field-value-long">'.e($eq->contenido_datos).'</div>
                    </div>' : '')
                    .($asignacionVigente ? '
                    <div class="grid-2" style="margin-top:4px;">
                        <div class="field">
                            <div class="field-label">Fecha de baja</div>
                            <div class="field-value">'.($asignacionVigente->fecha_inicio?->format('d/m/Y H:i') ?? '-').'</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Notas de baja</div>
                            <div class="field-value">'.e($asignacionVigente->notas ?? '-').'</div>
                        </div>
                    </div>' : '')
                    .'
                </div>';
            }

            $mpdf->WriteHTML($style.'
                '.$logoHtml.'
                <h1>'.e($tenant->razon_social).'</h1>
                <p style="color:#666;">RUC: '.e($tenant->ruc).'</p>

                <div class="ficha">
                    <div class="ficha-header">
                        <h2 style="margin:0;">F13 — Destruccion de Activo</h2>
                        <p style="margin:4px 0 0; color:#666;">N° '.e($r->numero_registro).' | Politica: PSC000003 / PSC000004</p>
                    </div>
                    <div class="ficha-body">
                        <div class="grid-3">
                            <div class="field">
                                <div class="field-label">Fecha de destruccion</div>
                                <div class="field-value">'.e($datos['fecha_destruccion'] ?? '-').'</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Metodo de destruccion</div>
                                <div class="field-value">'.$metodoBadge.'</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Proxima revision</div>
                                <div class="field-value">'.e($datos['proxima_revision'] ?? 'No programada').'</div>
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="field">
                                <div class="field-label">Responsable de la destruccion</div>
                                <div class="field-value">'.e($datos['responsable'] ?? '-').'</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Autorizado por</div>
                                <div class="field-value">'.e($datos['autoriza'] ?? '-').'</div>
                            </div>
                        </div>
                        <div class="field">
                            <div class="field-label">Descripcion del activo destruido</div>
                            <div class="field-value-long">'.nl2br(e($datos['descripcion_activo'] ?? '-')).'</div>
                        </div>
                        '.(! empty($datos['observaciones']) ? '
                        <div class="field">
                            <div class="field-label">Observaciones / Motivo de baja</div>
                            <div class="field-value-long">'.nl2br(e($datos['observaciones'])).'</div>
                        </div>' : '').'

                        '.$equipoSection.'

                        <div class="grid-2" style="margin-top:10px;">
                            <div class="field">
                                <div class="field-label">Registrado por</div>
                                <div class="field-value">'.e($r->creador?->name ?? '-').'</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Fecha de registro</div>
                                <div class="field-value">'.$r->created_at->format('d/m/Y H:i').'</div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="footer">
                    Generado: '.now()->format('d/m/Y H:i').' | SecuriForm — PSC000003 / PSC000004
                </p>');
        }
    }

    private function metodoLabel(?string $metodo): string
    {
        if (! $metodo) {
            return 'Sin especificar';
        }

        return match ($metodo) {
            'borrado_seguro' => 'Borrado seguro',
            'destruccion_fisica' => 'Destruccion fisica',
            'desmagnetizacion' => 'Desmagnetizacion',
            'incineracion' => 'Incineracion',
            'trituracion' => 'Trituracion',
            'proveedor_certificado' => 'Proveedor certificado',
            'sin_especificar' => 'Sin especificar',
            default => ucfirst(str_replace('_', ' ', $metodo)),
        };
    }

    private function metodoBadge(?string $metodo): string
    {
        if (! $metodo) {
            return '<span class="badge badge-warning">Sin especificar</span>';
        }

        $class = match ($metodo) {
            'borrado_seguro' => 'badge-info',
            'destruccion_fisica' => 'badge-danger',
            'desmagnetizacion' => 'badge-purple',
            'incineracion' => 'badge-danger',
            'trituracion' => 'badge-danger',
            'proveedor_certificado' => 'badge-success',
            default => 'badge-warning',
        };

        return '<span class="badge '.$class.'">'.e($this->metodoLabel($metodo)).'</span>';
    }

    private function motivoLabel(string $motivo): string
    {
        return match ($motivo) {
            'obsoleto' => 'Obsoleto',
            'danado' => 'Danado',
            'destruido' => 'Destruido',
            'perdido' => 'Perdido',
            'robado' => 'Robado',
            'otro' => 'Otro / No especificado',
            default => ucfirst($motivo),
        };
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
