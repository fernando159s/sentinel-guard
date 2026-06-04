<?php

namespace App\Support;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Models\ActivoDigital;
use App\Models\Empresa;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;

/**
 * Generador de reportes PDF de activos digitales con branding por empresa.
 * IMPORTANTE: nunca incluye credenciales (usuario/password/2FA).
 */
class ActivoDigitalPdf
{
    private static function mpdf(string $format = 'A4'): Mpdf
    {
        return new Mpdf([
            'format' => $format,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => storage_path('app/temp'),
        ]);
    }

    private static function header(Empresa $tenant, bool $hasLogo, string $subtitulo): string
    {
        $logoHtml = PdfBranding::logoHtml($hasLogo, 34);

        return $logoHtml.'
            <h1>'.e($tenant->razon_social).' <span style="font-size:9px;color:#666;font-weight:normal;">RUC: '.e($tenant->ruc).'</span></h1>
            <h2>'.e($subtitulo).'</h2>
            <p style="color:#666;font-size:8px;margin:0 0 6px;">Generado: '.now()->format('d/m/Y H:i').'</p>';
    }

    /** Reporte de inventario: estadisticas + tabla general. */
    public static function inventario(Empresa $tenant, Collection $activos): ?string
    {
        if ($activos->isEmpty()) {
            return null;
        }

        $mpdf = self::mpdf('A4-L');
        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);
        $colors = PdfBranding::colors($tenant);
        $style = self::style($colors);

        $total = $activos->count();
        $activos7 = $activos->filter(fn ($a) => $a->porVencer(30))->count();
        $vencidos = $activos->filter(fn ($a) => $a->estaVencido())->count();
        $costoMensual = $activos
            ->filter(fn ($a) => $a->modalidad_pago === ModalidadPago::Mensual)
            ->sum(fn ($a) => (float) $a->costo);

        $porTipo = $activos->groupBy(fn ($a) => $a->tipo?->value)->map->count()->sortDesc();
        $tipoRows = '';
        foreach ($porTipo as $tipo => $count) {
            $label = $tipo ? TipoActivoDigital::from($tipo)->label() : 'Sin tipo';
            $tipoRows .= '<tr><td>'.e($label).'</td><td style="text-align:center;">'.$count.'</td></tr>';
        }

        $rows = '';
        foreach ($activos as $a) {
            $resp = $a->responsables->pluck('name')->implode(', ');
            $venc = $a->fecha_vencimiento?->format('d/m/Y') ?? '—';
            $costo = $a->costo ? $a->moneda.' '.number_format((float) $a->costo, 2) : '—';
            $rows .= '<tr>'
                .'<td>'.e($a->codigo_interno).'</td>'
                .'<td>'.e($a->nombre).'</td>'
                .'<td>'.e($a->tipo?->label() ?? '—').'</td>'
                .'<td>'.e($a->proveedor ?? '—').'</td>'
                .'<td>'.e($a->modalidad_pago?->label() ?? '—').'</td>'
                .'<td style="text-align:right;">'.e($costo).'</td>'
                .'<td style="text-align:center;">'.e($venc).'</td>'
                .'<td>'.e($a->estado?->label() ?? '—').'</td>'
                .'<td>'.e($resp ?: '—').'</td>'
                .'</tr>';
        }

        $mpdf->WriteHTML($style.
            self::header($tenant, $hasLogo, 'Inventario de Activos Digitales').'
            <div class="stat-grid"><table><tr>'
                .self::statCell($total, 'Total cuentas', $colors)
                .self::statCell($vencidos, 'Vencidas', $colors)
                .self::statCell($activos7, 'Por vencer (30d)', $colors)
                .self::statCell('S/ '.number_format($costoMensual, 2), 'Costo mensual', $colors)
            .'</tr></table></div>

            <div style="width:32%;">
                <h3>Por tipo</h3>
                <table><tr><th>Tipo</th><th style="text-align:center;">Cant.</th></tr>'.$tipoRows.'</table>
            </div>

            <h3>Detalle de cuentas</h3>
            <table>
                <thead><tr>
                    <th>Codigo</th><th>Nombre</th><th>Tipo</th><th>Proveedor</th><th>Pago</th>
                    <th style="text-align:right;">Costo</th><th style="text-align:center;">Vence</th><th>Estado</th><th>Responsables</th>
                </tr></thead>
                <tbody>'.$rows.'</tbody>
            </table>');

        return $mpdf->Output('', 'S');
    }

    /** Reporte de vencimientos: solo vencidas + por vencer (30 dias). */
    public static function vencimientos(Empresa $tenant, Collection $activos): ?string
    {
        $filtrados = $activos
            ->filter(fn ($a) => $a->estaVencido() || $a->porVencer(30))
            ->sortBy('fecha_vencimiento');

        if ($filtrados->isEmpty()) {
            return null;
        }

        $mpdf = self::mpdf('A4');
        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);
        $style = self::style(PdfBranding::colors($tenant));

        $rows = '';
        foreach ($filtrados as $a) {
            $estado = $a->estaVencido() ? 'VENCIDO' : 'Por vencer';
            $clase = $a->estaVencido() ? 'badge-vencido' : 'badge-porvencer';
            $rows .= '<tr>'
                .'<td>'.e($a->codigo_interno).'</td>'
                .'<td>'.e($a->nombre).'</td>'
                .'<td>'.e($a->proveedor ?? '—').'</td>'
                .'<td style="text-align:center;">'.e($a->fecha_vencimiento?->format('d/m/Y') ?? '—').'</td>'
                .'<td><span class="badge '.$clase.'">'.$estado.'</span></td>'
                .'<td>'.e($a->responsables->pluck('name')->implode(', ') ?: '—').'</td>'
                .'</tr>';
        }

        $mpdf->WriteHTML($style.
            self::header($tenant, $hasLogo, 'Vencimientos y renovaciones de activos digitales').'
            <table>
                <thead><tr>
                    <th>Codigo</th><th>Nombre</th><th>Proveedor</th><th style="text-align:center;">Vence</th><th>Estado</th><th>Responsables</th>
                </tr></thead>
                <tbody>'.$rows.'</tbody>
            </table>');

        return $mpdf->Output('', 'S');
    }

    /** Ficha individual de una cuenta con historial de pagos. SIN credenciales. */
    public static function ficha(Empresa $tenant, ActivoDigital $activo): string
    {
        $mpdf = self::mpdf('A4');
        $hasLogo = PdfBranding::attachLogo($mpdf, $tenant);
        $style = self::style(PdfBranding::colors($tenant));

        $campo = fn (string $l, ?string $v): string => '<tr><td style="width:35%;color:#666;">'.e($l).'</td><td><strong>'.e($v ?: '—').'</strong></td></tr>';

        $pagoRows = '';
        foreach ($activo->pagos as $p) {
            $pagoRows .= '<tr>'
                .'<td>'.e($p->fecha_pago->format('d/m/Y')).'</td>'
                .'<td style="text-align:right;">'.e($p->moneda.' '.number_format((float) $p->monto, 2)).'</td>'
                .'<td>'.e($p->metodo ?? '—').'</td>'
                .'<td>'.e($p->registradoPor?->name ?? '—').'</td>'
                .'</tr>';
        }
        $pagosTabla = $pagoRows
            ? '<h3>Historial de pagos</h3><table><thead><tr><th>Fecha</th><th style="text-align:right;">Monto</th><th>Metodo</th><th>Registrado por</th></tr></thead><tbody>'.$pagoRows.'</tbody></table>'
            : '<p style="color:#666;font-size:9px;">Sin pagos registrados.</p>';

        $mpdf->WriteHTML($style.
            self::header($tenant, $hasLogo, 'Ficha de activo digital — '.$activo->codigo_interno).'
            <table>'
                .$campo('Nombre', $activo->nombre)
                .$campo('Tipo', $activo->tipo?->label())
                .$campo('Proveedor', $activo->proveedor)
                .$campo('Identificador', $activo->identificador)
                .$campo('URL', $activo->url)
                .$campo('Modalidad de pago', $activo->modalidad_pago?->label())
                .$campo('Costo', $activo->costo ? $activo->moneda.' '.number_format((float) $activo->costo, 2) : null)
                .$campo('Proximo vencimiento', $activo->fecha_vencimiento?->format('d/m/Y'))
                .$campo('Estado', $activo->estado?->label())
                .$campo('Sensibilidad', ucfirst($activo->nivel_sensibilidad))
                .$campo('Responsables', $activo->responsables->pluck('name')->implode(', '))
            .'</table>
            <p style="font-size:7.5px;color:#999;margin-top:4px;">Por seguridad, este documento no incluye credenciales de acceso.</p>
            '.$pagosTabla);

        return $mpdf->Output('', 'S');
    }

    private static function statCell(string|int $number, string $label, array $colors): string
    {
        return '<td style="border:1px solid #e5e7eb;background:#f9fafb;">'
            .'<div class="stat-number" style="color:'.$colors['primario'].';">'.$number.'</div>'
            .'<div class="stat-label">'.e($label).'</div></td>';
    }

    private static function style(array $colors): string
    {
        return PdfBranding::reportStyle(null).'
        <style>
            body { font-size: 8.5px; }
            h1 { font-size: 13px; }
            h2 { font-size: 10px; margin-top: 6px; }
            h3 { font-size: 9.5px; margin-top: 8px; }
            table { width: 100%; border-collapse: collapse; margin-top: 4px; }
            th, td { padding: 3px 5px; border: 1px solid #e5e7eb; }
            th { background: '.$colors['primario'].'15; font-size: 8px; text-align: left; }
            .stat-grid table { margin-top: 2px; }
            .stat-grid td { text-align: center; padding: 6px 8px; }
            .stat-number { font-size: 15px; font-weight: bold; }
            .stat-label { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
            .badge { padding: 1px 5px; border-radius: 6px; font-size: 7.5px; font-weight: bold; }
            .badge-vencido { background: #fee2e2; color: #b91c1c; }
            .badge-porvencer { background: #fef3c7; color: #b45309; }
        </style>';
    }
}
