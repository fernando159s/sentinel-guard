<?php

namespace App\Filament\Pages;

use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
use App\Models\Equipo;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteChecklists extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Reporte Checklists';

    protected static ?string $title = 'Reporte de Checklists de Equipos';

    protected string $view = 'filament.pages.reporte-checklists';

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

    public function generateReport(): ?StreamedResponse
    {
        $tenant = Filament::getTenant();

        $plantillas = ChecklistPlantilla::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->orderBy('nombre')
            ->get();

        $equipoIds = Equipo::withoutGlobalScopes()
            ->where('empresa_id', $tenant->id)
            ->pluck('id');

        $ejecuciones = ChecklistEjecucion::query()
            ->whereIn('equipo_id', $equipoIds)
            ->with(['plantilla', 'equipo', 'ejecutor'])
            ->orderBy('fecha_ejecucion', 'desc')
            ->get();

        if ($ejecuciones->isEmpty() && $plantillas->isEmpty()) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No hay checklists ni ejecuciones registradas para esta empresa.')
                ->warning()
                ->send();

            return null;
        }

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

        $this->buildReport($mpdf, $tenant, $plantillas, $ejecuciones);

        $filename = 'reporte_checklists_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildReport(Mpdf $mpdf, $tenant, $plantillas, $ejecuciones): void
    {
        $logoHtml = '';
        if ($tenant->logo_path && file_exists(storage_path('app/'.$tenant->logo_path))) {
            $logoHtml = '<img src="var:logo" style="height:50px;margin-bottom:8px;" /><br>';
        }

        $style = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
            h1 { color: #4338ca; font-size: 16px; margin-bottom: 2px; }
            h2 { font-size: 13px; margin-top: 20px; color: #333; }
            h3 { font-size: 11px; margin-top: 12px; color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
            th { background: #f3f4f6; font-weight: bold; }
            .summary { margin-top: 12px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
            .badge-ok { background: #d1fae5; color: #059669; }
            .badge-warn { background: #fef3c7; color: #d97706; }
            .badge-bad { background: #fee2e2; color: #dc2626; }
            .ficha { border: 1px solid #ddd; border-radius: 4px; margin-top: 12px; }
            .ficha-header { background: #f0f0ff; padding: 8px 10px; border-bottom: 1px solid #ddd; }
            .ficha-body { padding: 10px; }
            .footer { margin-top: 30px; font-size: 9px; color: #888; }
        </style>';

        $totalEjec = $ejecuciones->count();
        $totalEq = $ejecuciones->pluck('equipo_id')->unique()->count();

        $plantillaRows = '';
        foreach ($plantillas as $p) {
            $ejecsPlantilla = $ejecuciones->where('checklist_plantilla_id', $p->id);
            $plantillaRows .= '<tr>
                <td>'.e($p->nombre).'</td>
                <td>'.e($p->periodicidad ?? '-').'</td>
                <td style="text-align:center;">'.count($p->items ?? []).'</td>
                <td style="text-align:center;">'.$ejecsPlantilla->count().'</td>
                <td>'.($p->activa ? '<span class="badge badge-ok">Activa</span>' : '<span class="badge badge-bad">Inactiva</span>').'</td>
            </tr>';
        }

        $ejecRows = '';
        foreach ($ejecuciones as $e) {
            $cumple = $e->itemsCumplen();
            $total = $e->totalItems();
            $pct = $total > 0 ? round($cumple / $total * 100) : 0;
            $estClass = $pct >= 80 ? 'badge-ok' : ($pct >= 50 ? 'badge-warn' : 'badge-bad');

            $ejecRows .= '<tr>
                <td>'.$e->fecha_ejecucion->format('d/m/Y H:i').'</td>
                <td>'.e($e->plantilla?->nombre ?? '-').'</td>
                <td>'.e($e->equipo?->codigo_interno ?? '-').'</td>
                <td>'.e(trim(($e->equipo?->marca ?? '').' '.($e->equipo?->modelo ?? '')) ?: '-').'</td>
                <td>'.e($e->ejecutor?->name ?? '-').'</td>
                <td>'.e($e->estado ?? '-').'</td>
                <td><span class="badge '.$estClass.'">'.$cumple.'/'.$total.' ('.$pct.'%)</span></td>
            </tr>';
        }

        $mpdf->WriteHTML($style.'
            '.$logoHtml.'
            <h1>'.e($tenant->razon_social).'</h1>
            <p style="color:#666;">RUC: '.e($tenant->ruc).'</p>
            <p><strong>Reporte completo de Checklists de Equipos</strong></p>

            <div class="summary">
                <strong>Resumen:</strong>
                Plantillas: '.$plantillas->count().' |
                Ejecuciones: '.$totalEjec.' |
                Equipos verificados: '.$totalEq.'
            </div>

            <h2>Plantillas de checklist</h2>
            <table>
                <tr><th>Nombre</th><th>Periodicidad</th><th style="text-align:center;">Items</th><th style="text-align:center;">Ejecuciones</th><th>Estado</th></tr>
                '.($plantillaRows ?: '<tr><td colspan="5" style="text-align:center;color:#999;">Sin plantillas</td></tr>').'
            </table>

            <h2>Historial de ejecuciones — '.$totalEjec.'</h2>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Plantilla</th>
                    <th>Codigo equipo</th>
                    <th>Marca/Modelo</th>
                    <th>Ejecutado por</th>
                    <th>Estado</th>
                    <th>Score</th>
                </tr>
                '.($ejecRows ?: '<tr><td colspan="7" style="text-align:center;color:#999;">Sin ejecuciones</td></tr>').'
            </table>

            <p class="footer">
                Generado: '.now()->format('d/m/Y H:i').' | '.e(auth()->user()->name).' | SecuriForm
            </p>');

        // Detalle por ejecucion
        foreach ($ejecuciones as $e) {
            $mpdf->AddPage();

            $cumple = $e->itemsCumplen();
            $total = $e->totalItems();
            $pct = $total > 0 ? round($cumple / $total * 100) : 0;
            $estClass = $pct >= 80 ? 'badge-ok' : ($pct >= 50 ? 'badge-warn' : 'badge-bad');

            $itemsRows = '';
            foreach (($e->resultados ?? []) as $idx => $r) {
                $cumpleVal = ! empty($r['cumple']);
                $itemsRows .= '<tr>
                    <td style="text-align:center;width:30px;">'.($idx + 1).'</td>
                    <td>'.e($r['item'] ?? $r['nombre'] ?? '-').'</td>
                    <td style="text-align:center;width:60px;">'.($cumpleVal ? '<span class="badge badge-ok">Si</span>' : '<span class="badge badge-bad">No</span>').'</td>
                    <td>'.e($r['observacion'] ?? $r['observaciones'] ?? '').'</td>
                </tr>';
            }

            $mpdf->WriteHTML($style.'
                '.$logoHtml.'
                <h1>'.e($tenant->razon_social).'</h1>
                <p style="color:#666;">RUC: '.e($tenant->ruc).'</p>

                <div class="ficha">
                    <div class="ficha-header">
                        <h2 style="margin:0;">Ejecucion de Checklist</h2>
                        <p style="margin:4px 0 0; color:#666;">'.e($e->plantilla?->nombre ?? '-').' — '.$e->fecha_ejecucion->format('d/m/Y H:i').'</p>
                    </div>
                    <div class="ficha-body">
                        <table style="border:none;">
                            <tr style="border:none;">
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>Equipo:</strong> '.e($e->equipo?->codigo_interno ?? '-').'</td>
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>Marca/Modelo:</strong> '.e(trim(($e->equipo?->marca ?? '').' '.($e->equipo?->modelo ?? '')) ?: '-').'</td>
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>S/N:</strong> '.e($e->equipo?->numero_serie ?? '-').'</td>
                            </tr>
                            <tr style="border:none;">
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>Ejecutor:</strong> '.e($e->ejecutor?->name ?? '-').'</td>
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>Estado:</strong> '.e($e->estado ?? '-').'</td>
                                <td style="border:none;padding:2px 16px 2px 0;"><strong>Score:</strong> <span class="badge '.$estClass.'">'.$cumple.'/'.$total.' ('.$pct.'%)</span></td>
                            </tr>
                        </table>

                        <h3>Items verificados</h3>
                        <table>
                            <tr><th style="width:30px;">#</th><th>Item</th><th style="width:60px;">Cumple</th><th>Observaciones</th></tr>
                            '.($itemsRows ?: '<tr><td colspan="4" style="text-align:center;color:#999;">Sin items</td></tr>').'
                        </table>
                        '.(! empty($e->observaciones_generales) ? '
                        <h3>Observaciones generales</h3>
                        <p style="padding:6px 8px;background:#fafafa;border:1px solid #eee;">'.nl2br(e($e->observaciones_generales)).'</p>' : '').'
                    </div>
                </div>

                <p class="footer">
                    Generado: '.now()->format('d/m/Y H:i').' | SecuriForm
                </p>');
        }
    }
}
