<?php

namespace App\Filament\Pages;

use App\Models\EquipoAsignacion;
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
        $bytes = $this->pdfBytes($tenant);

        if ($bytes === null) {
            Notification::make()
                ->title('Sin resultados')
                ->body('No se encontraron movimientos.')
                ->warning()
                ->send();

            return null;
        }

        $filename = 'movimientos_soportes_F08_'.now()->format('Ymd').'.pdf';

        return response()->streamDownload(function () use ($bytes) {
            echo $bytes;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function pdfBytes($tenant, array $filtros = []): ?string
    {
        $tipoMov = $filtros['tipo_movimiento'] ?? ($this->data['tipo_movimiento'] ?? null);

        $query = EquipoAsignacion::query()
            ->whereHas('equipo', fn ($q) => $q->withoutGlobalScopes()->where('empresa_id', $tenant->id))
            ->with(['equipo', 'user', 'asignador']);

        if (! empty($tipoMov)) {
            $query->where('tipo', $tipoMov);
        }

        $movimientos = $query->orderBy('fecha_inicio')->get();

        if ($movimientos->isEmpty()) {
            return null;
        }

        $mpdf = new Mpdf([
            'format' => 'A4-L',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => storage_path('app/temp'),
        ]);

        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);

        $this->buildReport($mpdf, $tenant, $movimientos, $hasLogo);

        return $mpdf->Output('', 'S');
    }

    private function buildReport(Mpdf $mpdf, $tenant, $movimientos, bool $hasLogo = false): void
    {
        $logoHtml = PdfBranding::logoHtml($hasLogo, 36);
        $style = PdfBranding::reportStyle($tenant);

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
