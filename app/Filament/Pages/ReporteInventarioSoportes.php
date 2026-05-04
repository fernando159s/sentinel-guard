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

    protected static bool $shouldRegisterNavigation = false;

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
            'nivel_sensibilidad' => '',
            'tipo' => '',
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
                Select::make('nivel_sensibilidad')
                    ->label('Sensibilidad')
                    ->options([
                        '' => 'Todos',
                        'publico' => 'Publico',
                        'interno' => 'Interno',
                        'confidencial' => 'Confidencial',
                        'sensible' => 'Sensible',
                    ]),
                Select::make('tipo')
                    ->label('Tipo de equipo')
                    ->options([
                        '' => 'Todos',
                        'pc_escritorio' => 'PC de escritorio',
                        'laptop' => 'Laptop',
                        'impresora' => 'Impresora',
                        'servidor' => 'Servidor',
                        'usb' => 'Dispositivo USB',
                        'disco_externo' => 'Disco externo',
                        'telefono' => 'Telefono',
                        'tablet' => 'Tablet',
                        'dispositivo_red' => 'Dispositivo de red',
                        'dvd_cd' => 'DVD / CD',
                        'expediente_fisico' => 'Expediente fisico',
                        'soporte_nube' => 'Servicio cloud',
                        'otro' => 'Otro',
                    ]),
            ])
            ->statePath('data');
    }

    public function generateReport(): \Symfony\Component\HttpFoundation\StreamedResponse|null
    {
        $tenant = Filament::getTenant();

        $query = Equipo::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->with(['asignacionVigente.user']);

        if (! empty($this->data['categoria'])) {
            $query->where('categoria', $this->data['categoria']);
        }
        if (! empty($this->data['clasificacion_soporte'])) {
            $query->where('clasificacion_soporte', $this->data['clasificacion_soporte']);
        }
        if (! empty($this->data['estado'])) {
            $query->where('estado', $this->data['estado']);
        }
        if (! empty($this->data['nivel_sensibilidad'])) {
            $query->where('nivel_sensibilidad', $this->data['nivel_sensibilidad']);
        }
        if (! empty($this->data['tipo'])) {
            $query->where('tipo', $this->data['tipo']);
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
            $logoHtml = '<img src="var:logo" style="height:36px;margin-bottom:4px;" /><br>';
        }

        $style = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 8px; color: #333; }
            h1 { color: #4338ca; font-size: 14px; margin-bottom: 0; }
            h2 { font-size: 11px; margin-top: 8px; margin-bottom: 2px; color: #333; }
            h3 { font-size: 9px; margin-top: 6px; margin-bottom: 2px; color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 4px; }
            th, td { border: 1px solid #ddd; padding: 2px 4px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; font-size: 7px; }
            .stat-grid { margin-top: 6px; }
            .stat-grid table { margin-top: 2px; }
            .stat-grid td { text-align: center; padding: 4px 6px; }
            .stat-number { font-size: 16px; font-weight: bold; color: #4338ca; }
            .stat-label { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
            .summary { margin-top: 6px; padding: 4px 8px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 8px; }
            .badge { display: inline-block; padding: 1px 4px; border-radius: 6px; font-size: 7px; font-weight: bold; }
            .badge-tec { background: #dbeafe; color: #1d4ed8; }
            .badge-notec { background: #fef3c7; color: #d97706; }
            .badge-activo { background: #d1fae5; color: #059669; }
            .badge-mant { background: #fef3c7; color: #d97706; }
            .badge-obs { background: #fed7aa; color: #c2410c; }
            .badge-baja { background: #fee2e2; color: #dc2626; }
            .badge-publico { background: #e0e7ff; color: #4338ca; }
            .badge-interno { background: #dbeafe; color: #1d4ed8; }
            .badge-confidencial { background: #fef3c7; color: #d97706; }
            .badge-sensible { background: #fee2e2; color: #dc2626; }
            .ficha { border: 1px solid #ddd; border-radius: 4px; margin-top: 8px; }
            .ficha-header { background: #eef2ff; padding: 6px 10px; border-bottom: 1px solid #ddd; }
            .ficha-body { padding: 8px 10px; }
            .field { margin-bottom: 6px; }
            .field-label { font-size: 7px; font-weight: bold; color: #666; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }
            .field-value { font-size: 9px; color: #222; }
            .field-value-long { font-size: 9px; color: #222; padding: 4px 6px; background: #fafafa; border: 1px solid #eee; border-radius: 3px; }
            .grid-2 { display: flex; gap: 0; }
            .grid-2 > div { width: 50%; }
            .grid-3 { display: flex; gap: 0; }
            .grid-3 > div { width: 33.33%; }
            .grid-4 { display: flex; gap: 0; }
            .grid-4 > div { width: 25%; }
            .spec-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; padding: 6px 8px; margin-top: 6px; font-size: 8px; }
            .spec-box strong { color: #0369a1; }
            .asign-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 6px 8px; margin-top: 6px; font-size: 8px; }
            .asign-box strong { color: #15803d; }
            .footer { margin-top: 10px; font-size: 7px; color: #888; }
        </style>';

        $total = $equipos->count();
        $tecCount = $equipos->where('categoria', 'tecnologico')->count();
        $noTecCount = $equipos->where('categoria', 'no_tecnologico')->count();
        $asignados = $equipos->filter(fn ($eq) => $eq->asignacionVigente !== null)->count();
        $disponibles = $total - $asignados;

        // By estado
        $porEstado = $equipos->groupBy('estado')->map->count();
        // By tipo
        $porTipo = $equipos->groupBy('tipo')->map->count()->sortDesc();
        // By sensibilidad
        $porSensibilidad = $equipos->groupBy('nivel_sensibilidad')->map->count();
        // By clasificacion soporte
        $porClasificacion = $equipos->groupBy('clasificacion_soporte')->map->count()->filter(fn ($c, $k) => $k !== null && $k !== '');
        // Garantia vencida
        $garantiaVencida = $equipos->filter(fn ($eq) => $eq->fecha_garantia && $eq->fecha_garantia->isPast())->count();
        $garantiaVigente = $equipos->filter(fn ($eq) => $eq->fecha_garantia && $eq->fecha_garantia->isFuture())->count();
        $sinGarantia = $total - $garantiaVencida - $garantiaVigente;

        // ═══ PAGE 1: Summary + Statistics ═══
        $estadoRows = '';
        foreach ($porEstado as $estado => $count) {
            $pct = round($count / $total * 100);
            $estadoRows .= '<tr><td>' . e($this->estadoLabel($estado)) . '</td><td style="text-align:center;">' . $count . '</td><td style="text-align:center;">' . $pct . '%</td></tr>';
        }

        $tipoRows = '';
        foreach ($porTipo as $tipo => $count) {
            $pct = round($count / $total * 100);
            $tipoRows .= '<tr><td>' . e($this->tipoLabel($tipo)) . '</td><td style="text-align:center;">' . $count . '</td><td style="text-align:center;">' . $pct . '%</td></tr>';
        }

        $sensibilidadRows = '';
        foreach ($porSensibilidad as $nivel => $count) {
            $pct = round($count / $total * 100);
            $sensibilidadRows .= '<tr><td>' . e(ucfirst($nivel ?? 'Sin definir')) . '</td><td style="text-align:center;">' . $count . '</td><td style="text-align:center;">' . $pct . '%</td></tr>';
        }

        $clasificacionRows = '';
        foreach ($porClasificacion as $clas => $count) {
            $clasificacionRows .= '<tr><td>' . e($this->clasificacionLabel($clas)) . '</td><td style="text-align:center;">' . $count . '</td></tr>';
        }

        $mpdf->WriteHTML($style . '
            ' . $logoHtml . '
            <h1>' . e($tenant->razon_social) . ' <span style="font-size:9px;color:#666;font-weight:normal;">RUC: ' . e($tenant->ruc) . '</span></h1>
            <h2>Formato 7 — Inventario de Soportes</h2>
            <p style="color:#666;font-size:8px;margin:0;">Politica: PSC000003 / PSC000004 | Generado: ' . now()->format('d/m/Y H:i') . '</p>

            <div class="stat-grid">
                <table>
                    <tr>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $total . '</div>
                            <div class="stat-label">Total activos</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $tecCount . '</div>
                            <div class="stat-label">Tecnologicos</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $noTecCount . '</div>
                            <div class="stat-label">No tecnologicos</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $asignados . '</div>
                            <div class="stat-label">Asignados</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $disponibles . '</div>
                            <div class="stat-label">Disponibles</div>
                        </td>
                        <td style="border:1px solid #e5e7eb;background:#f9fafb;">
                            <div class="stat-number">' . $garantiaVencida . '</div>
                            <div class="stat-label">Garantia vencida</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="display:flex;gap:0;margin-top:6px;">
                <div style="width:25%;">
                    <h3>Por estado</h3>
                    <table>
                        <tr><th>Estado</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">%</th></tr>
                        ' . $estadoRows . '
                    </table>
                </div>
                <div style="width:30%;padding-left:10px;">
                    <h3>Por tipo de equipo</h3>
                    <table>
                        <tr><th>Tipo</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">%</th></tr>
                        ' . $tipoRows . '
                    </table>
                </div>
                <div style="width:22%;padding-left:10px;">
                    <h3>Por sensibilidad</h3>
                    <table>
                        <tr><th>Nivel</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">%</th></tr>
                        ' . $sensibilidadRows . '
                    </table>
                </div>
                <div style="width:23%;padding-left:10px;">
                    <h3>Por clasif. soporte</h3>
                    <table>
                        <tr><th>Clasificacion</th><th style="text-align:center;">Cant.</th></tr>
                        ' . $clasificacionRows . '
                    </table>
                    <h3 style="margin-top:4px;">Garantia</h3>
                    <table>
                        <tr><td>Vigente</td><td style="text-align:center;">' . $garantiaVigente . '</td></tr>
                        <tr><td>Vencida</td><td style="text-align:center;">' . $garantiaVencida . '</td></tr>
                        <tr><td>Sin registro</td><td style="text-align:center;">' . $sinGarantia . '</td></tr>
                    </table>
                </div>
            </div>

            <p class="footer">Generado por: ' . e(auth()->user()->name) . ' | SecuriForm — PSC000003 / PSC000004</p>');

        // ═══ PAGE 2: Full inventory table ═══
        $mpdf->AddPage('L');

        $rows = '';
        foreach ($equipos as $eq) {
            $catBadge = $eq->categoria === 'tecnologico'
                ? '<span class="badge badge-tec">Tec</span>'
                : '<span class="badge badge-notec">No tec</span>';

            $estadoBadge = $this->estadoBadge($eq->estado);
            $sensBadge = $this->sensibilidadBadge($eq->nivel_sensibilidad);
            $usuario = $eq->asignacionVigente?->user?->name ?? '-';
            $garantia = $eq->fecha_garantia
                ? ($eq->fecha_garantia->isPast()
                    ? '<span style="color:#dc2626;">' . $eq->fecha_garantia->format('d/m/Y') . '</span>'
                    : $eq->fecha_garantia->format('d/m/Y'))
                : '-';

            $rows .= '<tr>'
                . '<td>' . e($eq->codigo_interno ?? '-') . '</td>'
                . '<td>' . e($this->tipoLabel($eq->tipo)) . '</td>'
                . '<td>' . $catBadge . '</td>'
                . '<td>' . e($this->clasificacionLabel($eq->clasificacion_soporte)) . '</td>'
                . '<td>' . e(trim(($eq->marca ?? '') . ' ' . ($eq->modelo ?? '')) ?: '-') . '</td>'
                . '<td>' . e($eq->numero_serie ?? '-') . '</td>'
                . '<td>' . e($eq->ubicacion ?? '-') . '</td>'
                . '<td>' . $sensBadge . '</td>'
                . '<td>' . $estadoBadge . '</td>'
                . '<td>' . e($usuario) . '</td>'
                . '<td>' . ($eq->fecha_adquisicion?->format('d/m/Y') ?? '-') . '</td>'
                . '<td>' . $garantia . '</td>'
                . '</tr>';
        }

        $mpdf->WriteHTML($style . '
            ' . $logoHtml . '
            <h1>' . e($tenant->razon_social) . '</h1>
            <h2>Inventario completo de soportes — ' . $total . ' activos</h2>

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
                        <th>Sensibilidad</th>
                        <th>Estado</th>
                        <th>Asignado a</th>
                        <th>Adquisicion</th>
                        <th>Garantia</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>

            <p class="footer">
                Generado por: ' . e(auth()->user()->name) . ' | SecuriForm — PSC000003 / PSC000004
            </p>');

        // ═══ PAGES 3+: Individual detail cards per equipo ═══
        foreach ($equipos as $eq) {
            $mpdf->AddPage('P');

            $catBadge = $eq->categoria === 'tecnologico'
                ? '<span class="badge badge-tec">Tecnologico</span>'
                : '<span class="badge badge-notec">No tecnologico</span>';
            $estadoBadge = $this->estadoBadge($eq->estado);
            $sensBadge = $this->sensibilidadBadge($eq->nivel_sensibilidad);

            // Specs section (only for tech equipment)
            $specsSection = '';
            if ($eq->categoria === 'tecnologico' && ($eq->sistema_operativo || $eq->procesador || $eq->ram_gb || $eq->disco_gb)) {
                $specsSection = '
                <div class="spec-box">
                    <strong>Especificaciones tecnicas</strong>
                    <div class="grid-4" style="margin-top:6px;">
                        <div class="field">
                            <div class="field-label">Sistema operativo</div>
                            <div class="field-value">' . e($eq->sistema_operativo ?? '-') . '</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Procesador</div>
                            <div class="field-value">' . e($eq->procesador ?? '-') . '</div>
                        </div>
                        <div class="field">
                            <div class="field-label">RAM</div>
                            <div class="field-value">' . ($eq->ram_gb ? $eq->ram_gb . ' GB' : '-') . '</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Disco</div>
                            <div class="field-value">' . ($eq->disco_gb ? $eq->disco_gb . ' GB' : '-') . '</div>
                        </div>
                    </div>
                </div>';
            }

            // Current assignment
            $asignSection = '';
            $vigente = $eq->asignacionVigente;
            if ($vigente) {
                $asignSection = '
                <div class="asign-box">
                    <strong>Asignacion vigente</strong>
                    <div class="grid-3" style="margin-top:6px;">
                        <div class="field">
                            <div class="field-label">Asignado a</div>
                            <div class="field-value">' . e($vigente->user?->name ?? '-') . '</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Desde</div>
                            <div class="field-value">' . ($vigente->fecha_inicio?->format('d/m/Y') ?? '-') . '</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Condicion entrega</div>
                            <div class="field-value">' . e(ucfirst($vigente->condicion_entrega ?? '-')) . '</div>
                        </div>
                    </div>'
                    . ($vigente->notas ? '
                    <div class="field" style="margin-top:4px;">
                        <div class="field-label">Notas</div>
                        <div class="field-value">' . e($vigente->notas) . '</div>
                    </div>' : '')
                    . '</div>';
            }

            // Assignment history
            $historial = $eq->asignaciones()
                ->with(['user', 'asignador'])
                ->orderBy('fecha_inicio', 'desc')
                ->limit(10)
                ->get();

            $historialSection = '';
            if ($historial->isNotEmpty()) {
                $histRows = '';
                foreach ($historial as $a) {
                    $tipoLabel = match ($a->tipo) {
                        'asignacion' => 'Asignacion',
                        'transferencia' => 'Transferencia',
                        'devolucion' => 'Devolucion',
                        'baja' => 'Baja',
                        'ingreso_nuevo' => 'Ingreso',
                        'salida_mantenimiento' => 'Mantenimiento',
                        'salida_homeoffice' => 'Home office',
                        'salida_terceros' => 'A terceros',
                        default => ucfirst($a->tipo),
                    };
                    $histRows .= '<tr>'
                        . '<td>' . e($tipoLabel) . '</td>'
                        . '<td>' . e($a->user?->name ?? '-') . '</td>'
                        . '<td>' . ($a->fecha_inicio?->format('d/m/Y') ?? '-') . '</td>'
                        . '<td>' . ($a->fecha_fin?->format('d/m/Y') ?? 'Vigente') . '</td>'
                        . '<td>' . e(ucfirst($a->condicion_entrega ?? '-')) . '</td>'
                        . '<td>' . e($a->asignador?->name ?? '-') . '</td>'
                        . '</tr>';
                }

                $historialSection = '
                <h3 style="margin-top:12px;">Historial de movimientos</h3>
                <table>
                    <thead>
                        <tr><th>Tipo</th><th>Usuario</th><th>Inicio</th><th>Fin</th><th>Condicion</th><th>Realizado por</th></tr>
                    </thead>
                    <tbody>' . $histRows . '</tbody>
                </table>';
            }

            $garantiaHtml = '-';
            if ($eq->fecha_garantia) {
                $garantiaHtml = $eq->fecha_garantia->format('d/m/Y');
                if ($eq->fecha_garantia->isPast()) {
                    $garantiaHtml = '<span style="color:#dc2626;font-weight:bold;">' . $garantiaHtml . ' (VENCIDA)</span>';
                } else {
                    $garantiaHtml = '<span style="color:#059669;">' . $garantiaHtml . '</span>';
                }
            }

            $mpdf->WriteHTML($style . '
                ' . $logoHtml . '
                <h1>' . e($tenant->razon_social) . '</h1>
                <p style="color:#666;">RUC: ' . e($tenant->ruc) . '</p>

                <div class="ficha">
                    <div class="ficha-header">
                        <h2 style="margin:0;">F07 — Ficha de Inventario</h2>
                        <p style="margin:4px 0 0; color:#666;">Activo: ' . e($eq->codigo_interno ?? 'Sin codigo') . ' | Politica: PSC000003 / PSC000004</p>
                    </div>
                    <div class="ficha-body">
                        <div class="grid-3">
                            <div class="field">
                                <div class="field-label">Tipo de equipo</div>
                                <div class="field-value">' . e($this->tipoLabel($eq->tipo)) . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Categoria</div>
                                <div class="field-value">' . $catBadge . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Estado</div>
                                <div class="field-value">' . $estadoBadge . '</div>
                            </div>
                        </div>
                        <div class="grid-4">
                            <div class="field">
                                <div class="field-label">Marca</div>
                                <div class="field-value">' . e($eq->marca ?? '-') . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Modelo</div>
                                <div class="field-value">' . e($eq->modelo ?? '-') . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">N° Serie</div>
                                <div class="field-value">' . e($eq->numero_serie ?? '-') . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Codigo interno</div>
                                <div class="field-value">' . e($eq->codigo_interno ?? '-') . '</div>
                            </div>
                        </div>
                        <div class="grid-3">
                            <div class="field">
                                <div class="field-label">Clasificacion soporte</div>
                                <div class="field-value">' . e($this->clasificacionLabel($eq->clasificacion_soporte)) . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Nivel de sensibilidad</div>
                                <div class="field-value">' . $sensBadge . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Ubicacion</div>
                                <div class="field-value">' . e($eq->ubicacion ?? '-') . '</div>
                            </div>
                        </div>
                        <div class="grid-3">
                            <div class="field">
                                <div class="field-label">Fecha de adquisicion</div>
                                <div class="field-value">' . ($eq->fecha_adquisicion?->format('d/m/Y') ?? '-') . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Vencimiento de garantia</div>
                                <div class="field-value">' . $garantiaHtml . '</div>
                            </div>
                            <div class="field">
                                <div class="field-label">Asignado a</div>
                                <div class="field-value">' . e($vigente?->user?->name ?? 'Sin asignar') . '</div>
                            </div>
                        </div>
                        ' . (! empty($eq->contenido_datos) ? '
                        <div class="field">
                            <div class="field-label">Contenido de datos</div>
                            <div class="field-value-long">' . nl2br(e($eq->contenido_datos)) . '</div>
                        </div>' : '') . '
                        ' . (! empty($eq->observaciones) ? '
                        <div class="field">
                            <div class="field-label">Observaciones</div>
                            <div class="field-value-long">' . nl2br(e($eq->observaciones)) . '</div>
                        </div>' : '') . '

                        ' . $specsSection . '
                        ' . $asignSection . '
                        ' . $historialSection . '
                    </div>
                </div>

                <p class="footer">
                    Generado: ' . now()->format('d/m/Y H:i') . ' | SecuriForm — PSC000003 / PSC000004
                </p>');
        }
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

    private function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'activo' => 'Activo',
            'mantenimiento' => 'En mantenimiento',
            'obsoleto' => 'Obsoleto',
            'dado_de_baja' => 'Dado de baja',
            default => ucfirst(str_replace('_', ' ', $estado)),
        };
    }

    private function estadoBadge(string $estado): string
    {
        $class = match ($estado) {
            'activo' => 'badge-activo',
            'mantenimiento' => 'badge-mant',
            'obsoleto' => 'badge-obs',
            'dado_de_baja' => 'badge-baja',
            default => 'badge-notec',
        };

        return '<span class="badge ' . $class . '">' . e($this->estadoLabel($estado)) . '</span>';
    }

    private function sensibilidadBadge(?string $nivel): string
    {
        if (! $nivel) {
            return '-';
        }

        $class = match ($nivel) {
            'publico' => 'badge-publico',
            'interno' => 'badge-interno',
            'confidencial' => 'badge-confidencial',
            'sensible' => 'badge-sensible',
            default => 'badge-notec',
        };

        return '<span class="badge ' . $class . '">' . ucfirst($nivel) . '</span>';
    }
}
