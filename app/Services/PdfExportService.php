<?php

namespace App\Services;

use App\Enums\TipoFormato;
use App\Models\Registro;
use App\Support\PdfBranding;
use Mpdf\Mpdf;

class PdfExportService
{
    public static function exportRegistro(Registro $registro): string
    {
        $tipo = TipoFormato::from($registro->tipo_formato);
        $empresa = $registro->empresa;
        $creador = $registro->creador;

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 14,
            'margin_right' => 14,
            'tempDir' => storage_path('app/temp'),
        ]);

        $hasLogo = PdfBranding::attachLogo($mpdf, $empresa);

        $html = self::buildRegistroHtml($registro, $tipo, $empresa, $creador, $hasLogo);

        $mpdf->SetTitle("{$tipo->prefix()}-{$registro->numero_registro}");
        $mpdf->WriteHTML($html);

        $filename = "registro_{$registro->numero_registro}.pdf";
        $path = storage_path("app/temp/{$filename}");
        $mpdf->Output($path, 'F');

        return $path;
    }

    private static function buildRegistroHtml(Registro $registro, TipoFormato $tipo, $empresa, $creador, bool $hasLogo = false): string
    {
        $datos = $registro->datos ?? [];
        $fieldsHtml = '';

        foreach ($datos as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));
            $displayValue = is_array($value) ? implode(', ', $value) : ($value === true ? 'Sí' : ($value === false ? 'No' : ($value ?? '-')));
            $fieldsHtml .= "<tr><td style='font-weight:bold;'>{$label}</td><td>{$displayValue}</td></tr>";
        }

        $style = PdfBranding::reportStyle($empresa);
        $logoHtml = PdfBranding::logoHtml($hasLogo, 42);

        return $style."
        {$logoHtml}
        <h1>{$empresa->razon_social}</h1>
        <p class='meta'>RUC: {$empresa->ruc} | {$empresa->direccion}</p>
        <h2>{$tipo->label()}</h2>
        <p><strong>N° Registro:</strong> {$registro->numero_registro} &nbsp;&nbsp; <strong>Política:</strong> {$tipo->psc()}</p>
        <table>{$fieldsHtml}</table>
        <p class='footer'>Creado por: ".($creador?->name ?? 'N/A')." | Fecha: {$registro->created_at->format('d/m/Y H:i')} | Exportado: ".now()->format('d/m/Y H:i').'</p>';
    }

    public static function exportTicket($ticket): string
    {
        $empresa = $ticket->empresa;
        $mensajes = $ticket->mensajes()->with('autor')->orderBy('created_at')->get();
        $isAgent = auth()->user()->hasRole(['super_admin', 'agente_helpdesk']);

        $mensajesHtml = '';
        foreach ($mensajes as $msg) {
            if ($msg->tipo === 'interno' && ! $isAgent) {
                continue;
            }
            $tipoLabel = $msg->tipo === 'interno' ? ' <em>(nota interna)</em>' : '';
            $mensajesHtml .= "
            <div style='margin-bottom:6px;padding:5px 8px;border:1px solid #e5e7eb;border-radius:3px;'>
                <p style='font-weight:bold;margin:0;font-size:9.5px;'>{$msg->autor->name}{$tipoLabel}</p>
                <p style='font-size:8px;color:#9ca3af;margin:1px 0;'>{$msg->created_at->format('d/m/Y H:i')}</p>
                <p style='margin:3px 0 0;font-size:9px;'>{$msg->contenido}</p>
            </div>";
        }

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 14,
            'margin_right' => 14,
            'tempDir' => storage_path('app/temp'),
        ]);

        $hasLogo = PdfBranding::attachLogo($mpdf, $empresa);
        $style = PdfBranding::reportStyle($empresa);
        $logoHtml = PdfBranding::logoHtml($hasLogo, 38);

        $html = $style."
        {$logoHtml}
        <h1>Ticket: {$ticket->numero_ticket}</h1>
        <p class='meta'>{$empresa->razon_social} | RUC: {$empresa->ruc}</p>
        <p><strong>Asunto:</strong> {$ticket->asunto}</p>
        <p><strong>Estado:</strong> {$ticket->estado} | <strong>Prioridad:</strong> {$ticket->prioridad} | <strong>Categoría:</strong> {$ticket->categoria}</p>
        <p><strong>Descripción:</strong></p>
        <p>{$ticket->descripcion}</p>
        <h2>Conversación</h2>
        {$mensajesHtml}";

        $mpdf->WriteHTML($html);

        $path = storage_path("app/temp/ticket_{$ticket->numero_ticket}.pdf");
        $mpdf->Output($path, 'F');

        return $path;
    }
}
