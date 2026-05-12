<?php

namespace App\Filament\Pages;

use App\Models\EquipoAsignacion;
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

class ReporteMovimientosSoportes extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin_empresa']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Movimientos Soportes (F08)';

    protected static ?string $title = 'Formato 8 — Ingreso y Salida de Soportes';

    protected string $view = 'filament.pages.reporte-movimientos-soportes';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tipo_movimiento' => '',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('tipo_movimiento')
                    ->label('Tipo movimiento (opcional)')
                    ->options([
                        '' => 'Todos',
                        'ingreso_nuevo' => 'Ingreso nuevo',
                        'asignacion' => 'Asignacion',
                        'transferencia' => 'Transferencia',
                        'devolucion' => 'Devolucion',
                        'salida_mantenimiento' => 'Mantenimiento',
                        'salida_homeoffice' => 'Home office',
                        'salida_terceros' => 'A terceros',
                        'baja' => 'Baja',
                    ]),
            ])
            ->statePath('data');
    }

    public function generateReport(): ?StreamedResponse
    {
        $tenant = Filament::getTenant();

        $query = EquipoAsignacion::query()
            ->whereHas('equipo', fn ($q) => $q->withoutGlobalScopes()->where('empresa_id', $tenant->id))
            ->with(['equipo', 'user', 'asignador']);

        if (! empty($this->data['tipo_movimiento'])) {
            $query->where('tipo', $this->data['tipo_movimiento']);
        }

        $movimientos = $query->orderBy('fecha_inicio')->get();

        if ($movimientos->isEmpty()) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No se encontraron movimientos.')
                ->warning()
                ->send();

            return null;
        }

        $mpdf = new Mpdf([
            'format' => 'A4-L',
            'tempDir' => storage_path('app/temp'),
        ]);

        if ($tenant->logo_path) {
            $logoPath = storage_path('app/'.$tenant->logo_path);
            if (file_exists($logoPath)) {
                $mpdf->imageVars['logo'] = file_get_contents($logoPath);
            }
        }

        $this->buildReport($mpdf, $tenant, $movimientos);

        $filename = 'movimientos_soportes_F08_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildReport(Mpdf $mpdf, $tenant, $movimientos): void
    {
        $logoHtml = '';
        if ($tenant->logo_path && file_exists(storage_path('app/'.$tenant->logo_path))) {
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
            .footer { margin-top: 20px; font-size: 8px; color: #888; }
        </style>';

        $tipoCount = $movimientos->groupBy('tipo')->map->count();

        $rows = '';
        foreach ($movimientos as $m) {
            $rows .= '<tr>'
                .'<td>'.$m->fecha_inicio->format('d/m/Y H:i').'</td>'
                .'<td>'.e($m->equipo?->codigo_interno ?? '-').'</td>'
                .'<td>'.e($this->tipoEquipoLabel($m->equipo?->tipo ?? '')).'</td>'
                .'<td>'.e($m->equipo?->numero_serie ?? '-').'</td>'
                .'<td>'.e($this->tipoMovimientoLabel($m->tipo)).'</td>'
                .'<td>'.e($m->user?->name ?? '-').'</td>'
                .'<td>'.e($m->empresa_tercera ?? '-').'</td>'
                .'<td style="max-width:100px;">'.e(mb_substr($m->motivo ?? '-', 0, 60)).'</td>'
                .'<td>'.e($m->condicion_entrega ?? '-').'</td>'
                .'<td>'.e($m->asignador?->name ?? '-').'</td>'
                .'</tr>';
        }

        $summaryParts = [];
        foreach ($tipoCount as $tipo => $count) {
            $summaryParts[] = $this->tipoMovimientoLabel($tipo).': '.$count;
        }

        $mpdf->WriteHTML($style.'
            '.$logoHtml.'
            <h1>'.e($tenant->razon_social).'</h1>
            <p style="color:#666;">RUC: '.e($tenant->ruc).'</p>
            <h2>Formato 8 — Ingreso y Salida de Soportes</h2>
            <p style="color:#666;">Reporte completo | Politica: PSC000003</p>

            <div class="summary">
                <strong>Resumen:</strong> Total: '.$movimientos->count().' movimientos | '.implode(' | ', $summaryParts).'
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Codigo</th>
                        <th>Tipo equipo</th>
                        <th>N° Serie</th>
                        <th>Movimiento</th>
                        <th>Usuario</th>
                        <th>Empresa tercera</th>
                        <th>Motivo</th>
                        <th>Condicion</th>
                        <th>Realizado por</th>
                    </tr>
                </thead>
                <tbody>'.$rows.'</tbody>
            </table>

            <p class="footer">
                Generado: '.now()->format('d/m/Y H:i').' | '.e(auth()->user()->name).' | SecuriForm — PSC000003
            </p>');
    }

    private function tipoMovimientoLabel(string $tipo): string
    {
        return match ($tipo) {
            'ingreso_nuevo' => 'Ingreso nuevo',
            'asignacion' => 'Asignacion',
            'transferencia' => 'Transferencia',
            'devolucion' => 'Devolucion',
            'salida_mantenimiento' => 'Mantenimiento',
            'salida_homeoffice' => 'Home office',
            'salida_terceros' => 'A terceros',
            'baja' => 'Baja',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }

    private function tipoEquipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'pc_escritorio' => 'PC',
            'laptop' => 'Laptop',
            'impresora' => 'Impresora',
            'servidor' => 'Servidor',
            'usb' => 'USB',
            'disco_externo' => 'Disco ext.',
            'telefono' => 'Telefono',
            'tablet' => 'Tablet',
            'dispositivo_red' => 'Red',
            'dvd_cd' => 'DVD/CD',
            'expediente_fisico' => 'Expediente',
            'soporte_nube' => 'Cloud',
            'otro' => 'Otro',
            default => $tipo,
        };
    }
}
