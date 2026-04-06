<?php

namespace App\Http\Controllers;

use App\Models\AceptacionPolitica;
use App\Models\Politica;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PoliticaPdfController extends Controller
{
    public function download(Request $request, Politica $politica)
    {
        $empresa = $politica->empresa;
        $user = auth()->user();

        // Check if user has accepted this version
        $aceptacion = AceptacionPolitica::where('politica_id', $politica->id)
            ->where('user_id', $user->id)
            ->where('version_aceptada', $politica->version)
            ->latest()
            ->first();

        // Admin firma (empresa admin)
        $adminFirma = null;
        $admin = \App\Models\User::where('empresa_id', $politica->empresa_id)
            ->role('admin_empresa')
            ->whereNotNull('firma_guardada')
            ->first();

        $html = '
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
            .sig-row { display: flex; width: 100%; }
            .sig-box { width: 48%; border: 1px solid #d1d5db; border-radius: 8px; padding: 15px; margin-bottom: 10px; }
            .sig-box h4 { font-size: 11px; color: #6b7280; text-transform: uppercase; margin: 0 0 10px; }
            .sig-img { height: 50px; margin-bottom: 8px; }
            .sig-name { font-weight: bold; font-size: 12px; border-top: 1px solid #1a1a1a; padding-top: 5px; margin-top: 5px; }
            .sig-detail { font-size: 10px; color: #6b7280; }
            .footer { margin-top: 30px; border-top: 1px solid #d1d5db; padding-top: 10px; font-size: 9px; color: #9ca3af; text-align: center; }
        </style>

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
                <tr><td class="label">Fecha:</td><td class="value">' . $politica->created_at->format('d/m/Y') . '</td></tr>
            </table>
        </div>

        <div class="content">' . $politica->contenido . '</div>

        <div class="signatures">
            <table width="100%"><tr>';

        // Admin/Employer signature
        $html .= '<td width="48%" valign="top">
            <div class="sig-box">
                <h4>Firma del empleador / Responsable</h4>';
        if ($admin?->firma_guardada && str_starts_with($admin->firma_guardada, 'data:image')) {
            $html .= '<img src="' . $admin->firma_guardada . '" class="sig-img">';
        } else {
            $html .= '<div style="height:50px;"></div>';
        }
        $html .= '<div class="sig-name">' . e($admin?->name ?? '________________________') . '</div>
                <div class="sig-detail">' . e($empresa?->razon_social ?? '') . '</div>
            </div>
        </td><td width="4%"></td>';

        // Employee signature
        $html .= '<td width="48%" valign="top">
            <div class="sig-box">
                <h4>Firma del trabajador</h4>';
        if ($aceptacion?->firma_imagen && str_starts_with($aceptacion->firma_imagen, 'data:image')) {
            $html .= '<img src="' . $aceptacion->firma_imagen . '" class="sig-img">';
            $html .= '<div class="sig-name">' . e($aceptacion->firma_nombre) . '</div>';
            if ($aceptacion->firma_cargo) {
                $html .= '<div class="sig-detail">' . e($aceptacion->firma_cargo) . '</div>';
            }
            $html .= '<div class="sig-detail">Firmado: ' . $aceptacion->fecha_aceptacion->format('d/m/Y H:i') . '</div>';
            $html .= '<div class="sig-detail">IP: ' . e($aceptacion->ip_address) . '</div>';
        } else {
            $html .= '<div style="height:50px;"></div>
                <div class="sig-name">________________________</div>
                <div class="sig-detail">Nombre y firma</div>';
        }
        $html .= '</div></td></tr></table></div>

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
}
