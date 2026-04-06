<?php

namespace App\Http\Controllers;

use App\Models\Politica;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PoliticaPdfController extends Controller
{
    public function download(Request $request, Politica $politica)
    {
        $empresa = $politica->empresa;

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
            .footer { margin-top: 40px; border-top: 1px solid #d1d5db; padding-top: 15px; font-size: 10px; color: #9ca3af; text-align: center; }
            .signature { margin-top: 60px; }
            .signature-line { border-top: 1px solid #1a1a1a; width: 250px; margin-top: 40px; padding-top: 5px; font-size: 11px; }
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
                <tr><td class="label">Fecha de creacion:</td><td class="value">' . $politica->created_at->format('d/m/Y') . '</td></tr>
            </table>
        </div>

        <div class="content">
            ' . $politica->contenido . '
        </div>

        <div class="signature">
            <p><strong>Firma de aceptacion:</strong></p>
            <div class="signature-line">Nombre completo y firma</div>
            <br>
            <div class="signature-line">Fecha</div>
        </div>

        <div class="footer">
            ' . e($empresa?->razon_social ?? '') . ' &mdash; Generado el ' . now()->format('d/m/Y H:i') . '
            <br>Este documento es confidencial y de uso interno.
        </div>';

        $mpdf = new Mpdf([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'margin_right' => 20,
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
