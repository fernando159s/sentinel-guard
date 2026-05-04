<?php

namespace App\Http\Controllers;

use App\Models\AceptacionPolitica;
use App\Models\Politica;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class PoliticaPdfController extends Controller
{
    public function download(Request $request, Politica $politica)
    {
        $empresa = $politica->empresa;
        $user = auth()->user();

        $aceptacion = AceptacionPolitica::where('politica_id', $politica->id)
            ->where('user_id', $user->id)
            ->where('version_aceptada', $politica->version)
            ->latest()
            ->first();

        $admin = $this->getAdminConFirma($politica->empresa_id);

        $html = $this->buildStyle();

        $html .= '
        <div class="header">
            <h1>' . e($empresa?->razon_social ?? 'SecuriForm') . '</h1>
            <p>Documento de politica de seguridad de la informacion</p>
        </div>

        <div class="meta">
            <table>
                <tr><td class="label">Titulo:</td><td class="value">' . e($politica->titulo) . '</td></tr>
                <tr><td class="label">Version:</td><td class="value">' . e($politica->version) . '</td></tr>
                <tr><td class="label">Estado:</td><td class="value">' . ($politica->activa ? 'Activa' : 'Inactiva') . '</td></tr>
                <tr><td class="label">Obligatoria:</td><td class="value">' . ($politica->obligatoria ? 'Si' : 'No') . '</td></tr>
                <tr><td class="label">Fecha:</td><td class="value">' . $politica->created_at->format('d/m/Y') . '</td></tr>'
                . ($politica->es_nda ? '<tr><td class="label">Tipo:</td><td class="value">Acuerdo de Confidencialidad (NDA)</td></tr>' : '')
                . ($politica->es_nda && $politica->vigencia_meses ? '<tr><td class="label">Vigencia:</td><td class="value">' . $politica->vigencia_meses . ' meses</td></tr>' : '')
                . ($aceptacion?->fecha_expiracion ? '<tr><td class="label">Expira:</td><td class="value">' . $aceptacion->fecha_expiracion->format('d/m/Y') . '</td></tr>' : '') . '
            </table>
        </div>

        <div class="content">' . $this->renderContenido($politica, $user) . '</div>';

        $html .= $this->buildSignatureBlock($admin, $aceptacion, $empresa);

        $html .= '
        <div class="footer">
            ' . e($empresa?->razon_social ?? '') . ' &mdash; Generado el ' . now()->format('d/m/Y H:i') . '
            <br>Este documento es confidencial y de uso interno.
        </div>';

        $mpdf = new Mpdf([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'margin_right' => 20,
            'tempDir' => storage_path('app/temp'),
        ]);

        $mpdf->SetTitle($politica->titulo . ' v' . $politica->version);
        $mpdf->WriteHTML($html);

        $filename = 'politica_' . $politica->slug . '_v' . $politica->version . '.pdf';
        $path = storage_path('app/temp/' . $filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    public function downloadNdaFirmante(Request $request, Politica $politica, AceptacionPolitica $aceptacion)
    {
        $empresa = $politica->empresa;
        $firmante = $aceptacion->user;
        $admin = $this->getAdminConFirma($politica->empresa_id);

        $html = $this->buildStyle();

        $html .= '
        <div class="header">
            <h1>' . e($empresa?->razon_social ?? 'SecuriForm') . '</h1>
            <p>Acuerdo de Confidencialidad (NDA)</p>
        </div>

        <div class="meta">
            <table>
                <tr><td class="label">Documento:</td><td class="value">' . e($politica->titulo) . '</td></tr>
                <tr><td class="label">Version:</td><td class="value">' . e($aceptacion->version_aceptada) . '</td></tr>
                <tr><td class="label">Firmante:</td><td class="value">' . e($firmante->name) . '</td></tr>
                <tr><td class="label">DNI:</td><td class="value">' . e($firmante->dni ?? 'N/A') . '</td></tr>
                <tr><td class="label">Puesto:</td><td class="value">' . e($firmante->puesto ?? 'N/A') . '</td></tr>
                <tr><td class="label">Fecha firma:</td><td class="value">' . $aceptacion->fecha_aceptacion->format('d/m/Y H:i') . '</td></tr>'
                . ($aceptacion->fecha_expiracion ? '<tr><td class="label">Expira:</td><td class="value">' . $aceptacion->fecha_expiracion->format('d/m/Y') . '</td></tr>' : '')
                . '<tr><td class="label">Estado:</td><td class="value">' . ($aceptacion->estaVigente() ? 'Vigente' : 'Expirado') . '</td></tr>
            </table>
        </div>

        <div class="content">' . $this->renderContenido($politica, $firmante) . '</div>';

        $html .= $this->buildSignatureBlock($admin, $aceptacion, $empresa);

        $html .= '
        <div class="footer">
            ' . e($empresa?->razon_social ?? '') . ' &mdash; Generado el ' . now()->format('d/m/Y H:i') . '
            <br>Este documento es confidencial y de uso interno.
        </div>';

        $mpdf = new Mpdf([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'margin_right' => 20,
            'tempDir' => storage_path('app/temp'),
        ]);

        $mpdf->SetTitle('NDA - ' . $firmante->name . ' - ' . $politica->titulo);
        $mpdf->WriteHTML($html);

        $filename = 'nda_' . Str::slug($firmante->name) . '_v' . $aceptacion->version_aceptada . '.pdf';
        $path = storage_path('app/temp/' . $filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    public function downloadResumenFirmantes(Request $request, Politica $politica)
    {
        $empresa = $politica->empresa;
        $admin = $this->getAdminConFirma($politica->empresa_id);

        $aceptaciones = AceptacionPolitica::where('politica_id', $politica->id)
            ->with('user')
            ->orderBy('version_aceptada', 'desc')
            ->orderBy('fecha_aceptacion', 'desc')
            ->get();

        $porVersion = $aceptaciones->groupBy('version_aceptada');

        $mpdf = new Mpdf([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'margin_right' => 20,
            'tempDir' => storage_path('app/temp'),
        ]);

        $style = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
            .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 18px; }
            .header h1 { font-size: 18px; margin: 0; color: #1e1b4b; }
            .header p { font-size: 11px; color: #6b7280; margin: 4px 0 0; }
            .meta { background: #f3f4f6; padding: 10px 15px; border-radius: 6px; margin-bottom: 18px; font-size: 10px; }
            .meta td { padding: 2px 0; }
            .meta .label { color: #6b7280; width: 130px; }
            .meta .value { font-weight: bold; }
            .version-title { font-size: 13px; font-weight: bold; color: #1e1b4b; margin: 18px 0 8px; padding: 6px 10px; background: #eef2ff; border-radius: 4px; }
            .firma-row { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px; margin-bottom: 8px; }
            .firma-row table { width: 100%; }
            .firma-img { max-height: 45px; max-width: 150px; }
            .firma-name { font-weight: bold; font-size: 11px; }
            .firma-detail { font-size: 9px; color: #6b7280; }
            .summary { font-size: 10px; color: #6b7280; margin-bottom: 12px; }
            .admin-box { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 18px; text-align: center; }
            .admin-box h4 { font-size: 10px; color: #6b7280; text-transform: uppercase; margin: 0 0 8px; }
            .admin-img { max-height: 60px; max-width: 200px; margin-bottom: 6px; }
            .admin-name { font-weight: bold; font-size: 12px; border-top: 1px solid #1a1a1a; padding-top: 5px; margin-top: 5px; display: inline-block; min-width: 160px; }
            .admin-detail { font-size: 9px; color: #6b7280; }
            .footer { margin-top: 20px; border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 8px; color: #9ca3af; text-align: center; }
        </style>';

        // ═══ PAGE 1: Header + Admin signature + Summary table ═══
        $html = $style;

        $html .= '
        <div class="header">
            <h1>' . e($empresa?->razon_social ?? 'SecuriForm') . '</h1>
            <p>Resumen de firmantes — ' . e($politica->titulo) . '</p>
        </div>

        <div class="meta">
            <table>
                <tr><td class="label">Politica:</td><td class="value">' . e($politica->titulo) . '</td></tr>
                <tr><td class="label">Version actual:</td><td class="value">' . e($politica->version) . '</td></tr>
                <tr><td class="label">Tipo:</td><td class="value">' . ($politica->es_nda ? 'Acuerdo de Confidencialidad (NDA)' : 'Politica de seguridad') . '</td></tr>
                <tr><td class="label">Total firmantes:</td><td class="value">' . $aceptaciones->count() . '</td></tr>
                <tr><td class="label">Generado:</td><td class="value">' . now()->format('d/m/Y H:i') . '</td></tr>
            </table>
        </div>';

        // Admin signature block
        $html .= '<div class="admin-box">';
        if ($admin?->firma_guardada && str_starts_with($admin->firma_guardada, 'data:image')) {
            $html .= '<h4>Firma del Gerente General</h4>
                <img src="' . $admin->firma_guardada . '" class="admin-img"><br>
                <div class="admin-name">' . e($admin->name) . '</div>
                <div class="admin-detail">Gerente General — ' . e($empresa?->razon_social ?? '') . '</div>';
        } else {
            $html .= '<h4>Firma del Gerente General</h4>
                <div style="height:40px;"></div>
                <div class="admin-name">________________________</div>
                <div class="admin-detail">Pendiente de firma</div>';
        }
        $html .= '</div>';

        // Firmantes by version
        foreach ($porVersion as $version => $firmas) {
            $html .= '<div class="version-title">Version ' . e($version) . ' — ' . $firmas->count() . ' firmante(s)</div>';

            foreach ($firmas as $a) {
                $user = $a->user;
                $html .= '<div class="firma-row"><table cellpadding="0" cellspacing="0"><tr>';

                // Left: user info
                $html .= '<td width="55%" valign="middle">
                    <div class="firma-name">' . e($user?->name ?? '-') . '</div>
                    <div class="firma-detail">' . e($user?->puesto ?? '-') . ' · DNI: ' . e($user?->dni ?? '-') . '</div>
                    <div class="firma-detail">Firmado: ' . $a->fecha_aceptacion->format('d/m/Y H:i');

                if ($politica->es_nda && $a->fecha_expiracion) {
                    $vigente = $a->estaVigente();
                    $html .= ' · Expira: ' . $a->fecha_expiracion->format('d/m/Y')
                        . ' <span style="color:' . ($vigente ? '#059669' : '#dc2626') . ';font-weight:bold;">(' . ($vigente ? 'Vigente' : 'Expirado') . ')</span>';
                }

                $html .= '</div></td>';

                // Right: signature
                $html .= '<td width="45%" valign="middle" style="text-align:right;">';
                if ($a->firma_imagen && str_starts_with($a->firma_imagen, 'data:image')) {
                    $html .= '<img src="' . $a->firma_imagen . '" class="firma-img">';
                    if ($a->firma_nombre) {
                        $html .= '<div class="firma-detail">' . e($a->firma_nombre) . '</div>';
                    }
                } else {
                    $html .= '<span class="firma-detail" style="color:#dc2626;">Sin firma digital</span>';
                }
                $html .= '</td></tr></table></div>';
            }
        }

        $html .= '<div class="footer">'
            . e($empresa?->razon_social ?? '') . ' — Resumen generado el ' . now()->format('d/m/Y H:i')
            . ' por ' . e(auth()->user()->name)
            . '<br>Este documento es confidencial y de uso interno.</div>';

        $mpdf->SetTitle('Firmantes - ' . $politica->titulo);
        $mpdf->WriteHTML($html);

        $filename = 'firmantes_' . $politica->slug . '_v' . $politica->version . '.pdf';
        $path = storage_path('app/temp/' . $filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    private function getAdminConFirma(int $empresaId): ?User
    {
        return User::where('empresa_id', $empresaId)
            ->role('admin_empresa')
            ->whereNotNull('firma_guardada')
            ->first();
    }

    private function buildStyle(): string
    {
        return '
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; margin-bottom: 20px; }
            .header h1 { font-size: 18px; margin: 0; color: #1e1b4b; }
            .header p { font-size: 11px; color: #6b7280; margin: 4px 0 0; }
            .meta { background: #f3f4f6; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 11px; }
            .meta table { width: 100%; }
            .meta td { padding: 3px 0; }
            .meta .label { color: #6b7280; width: 140px; }
            .meta .value { font-weight: bold; }
            .content { margin-top: 20px; }
            .content h2 { font-size: 16px; color: #1e1b4b; }
            .content h3 { font-size: 14px; color: #374151; }
            .content ul { padding-left: 20px; }
            .content li { margin-bottom: 4px; }
            .signatures { margin-top: 40px; }
            .sig-box { border: 1px solid #d1d5db; border-radius: 8px; padding: 20px; margin-bottom: 15px; text-align: center; }
            .sig-box h4 { font-size: 12px; color: #6b7280; text-transform: uppercase; margin: 0 0 15px; }
            .sig-img { max-width: 280px; max-height: 120px; margin: 0 auto 10px; display: block; }
            .sig-name { font-weight: bold; font-size: 13px; border-top: 1px solid #1a1a1a; padding-top: 8px; margin-top: 8px; display: inline-block; min-width: 200px; }
            .sig-detail { font-size: 10px; color: #6b7280; }
            .sig-missing { background: #fef2f2; border: 1px dashed #fca5a5; border-radius: 8px; padding: 20px; text-align: center; color: #dc2626; font-size: 11px; }
            .footer { margin-top: 30px; border-top: 1px solid #d1d5db; padding-top: 10px; font-size: 9px; color: #9ca3af; text-align: center; }
        </style>';
    }

    private function buildSignatureBlock(?User $admin, ?AceptacionPolitica $aceptacion, $empresa): string
    {
        $html = '<div class="signatures"><table width="100%" cellpadding="0" cellspacing="0"><tr>';

        // Admin / Gerente General — left 50%
        $html .= '<td width="48%" valign="top">';
        if ($admin?->firma_guardada && str_starts_with($admin->firma_guardada, 'data:image')) {
            $html .= '<div class="sig-box">
                <h4>Firma del Gerente General</h4>
                <img src="' . $admin->firma_guardada . '" class="sig-img">
                <div class="sig-name">' . e($admin->name) . '</div>
                <div class="sig-detail">Gerente General</div>
                <div class="sig-detail">' . e($empresa?->razon_social ?? '') . '</div>
            </div>';
        } else {
            $html .= '<div class="sig-missing">
                <p style="margin:0;font-weight:bold;">Pendiente de firma</p>
                <p style="margin:4px 0 0;font-size:10px;">El Gerente General debe subir su firma en su perfil.</p>
            </div>';
        }
        $html .= '</td><td width="4%"></td>';

        // Employee — right 50%
        $html .= '<td width="48%" valign="top">';
        if ($aceptacion?->firma_imagen && str_starts_with($aceptacion->firma_imagen, 'data:image')) {
            $html .= '<div class="sig-box">
                <h4>Firma del trabajador</h4>
                <img src="' . $aceptacion->firma_imagen . '" class="sig-img">
                <div class="sig-name">' . e($aceptacion->firma_nombre) . '</div>'
                . ($aceptacion->firma_cargo ? '<div class="sig-detail">' . e($aceptacion->firma_cargo) . '</div>' : '') . '
                <div class="sig-detail">Firmado: ' . $aceptacion->fecha_aceptacion->format('d/m/Y H:i') . '</div>
                <div class="sig-detail">IP: ' . e($aceptacion->ip_address) . '</div>
            </div>';
        } else {
            $html .= '<div class="sig-box">
                <h4>Firma del trabajador</h4>
                <div style="height:80px;"></div>
                <div class="sig-name">________________________</div>
                <div class="sig-detail">Nombre y firma</div>
            </div>';
        }
        $html .= '</td></tr></table></div>';

        return $html;
    }

    private function renderContenido(Politica $politica, $user): string
    {
        $contenido = $politica->contenido;

        if ($politica->es_nda) {
            $contenido = str_replace(
                ['{nombre_completo}', '{dni}', '{direccion}', '{telefono}', '{puesto}'],
                [
                    e($user->name),
                    e($user->dni ?? '___________'),
                    e($user->direccion ?? '___________'),
                    e($user->telefono ?? '___________'),
                    e($user->puesto ?? '___________'),
                ],
                $contenido
            );
        }

        return $contenido;
    }
}
