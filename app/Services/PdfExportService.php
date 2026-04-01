<?php

namespace App\Services;

use App\Enums\TipoFormato;
use App\Models\Registro;
use Mpdf\Mpdf;

class PdfExportService
{
    public static function exportRegistro(Registro $registro): string
    {
        $tipo = TipoFormato::from($registro->tipo_formato);
        $empresa = $registro->empresa;
        $creador = $registro->creador;

        $html = self::buildRegistroHtml($registro, $tipo, $empresa, $creador);

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 20,
            'margin_bottom' => 20,
            'tempDir' => storage_path('app/temp'),
        ]);

        $mpdf->SetTitle("{$tipo->prefix()}-{$registro->numero_registro}");
        $mpdf->WriteHTML($html);

        $filename = "registro_{$registro->numero_registro}.pdf";
        $path = storage_path("app/temp/{$filename}");
        $mpdf->Output($path, 'F');

        return $path;
    }

    private static function buildRegistroHtml(Registro $registro, TipoFormato $tipo, $empresa, $creador): string
    {
        $datos = $registro->datos ?? [];
        $fieldsHtml = '';

        foreach ($datos as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));
            $displayValue = is_array($value) ? implode(', ', $value) : ($value === true ? 'Sí' : ($value === false ? 'No' : ($value ?? '-')));
            $fieldsHtml .= "<tr><td style='font-weight:bold;padding:6px;border:1px solid #ddd;width:35%;'>{$label}</td><td style='padding:6px;border:1px solid #ddd;'>{$displayValue}</td></tr>";
        }

        return "
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
            h1 { color: #4338ca; font-size: 18px; margin-bottom: 5px; }
            h2 { color: #555; font-size: 14px; margin-top: 20px; }
            .header { border-bottom: 2px solid #4338ca; padding-bottom: 10px; margin-bottom: 20px; }
            .meta { color: #666; font-size: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 9px; color: #888; }
        </style>
        <div class='header'>
            <h1>{$empresa->razon_social}</h1>
            <p class='meta'>RUC: {$empresa->ruc} | {$empresa->direccion}</p>
        </div>
        <h2>{$tipo->label()}</h2>
        <p><strong>N° Registro:</strong> {$registro->numero_registro} &nbsp;&nbsp; <strong>Política:</strong> {$tipo->psc()}</p>
        <table>{$fieldsHtml}</table>
        <div class='footer'>
            <p>Creado por: {$creador->name} | Fecha: {$registro->created_at->format('d/m/Y H:i')} | Exportado: " . now()->format('d/m/Y H:i') . "</p>
        </div>";
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
            <div style='margin-bottom:10px;padding:8px;border:1px solid #ddd;border-radius:4px;'>
                <p style='font-weight:bold;margin:0;'>{$msg->autor->name}{$tipoLabel}</p>
                <p style='font-size:9px;color:#888;margin:2px 0;'>{$msg->created_at->format('d/m/Y H:i')}</p>
                <p style='margin:5px 0 0;'>{$msg->contenido}</p>
            </div>";
        }

        $html = "
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
            h1 { color: #4338ca; font-size: 16px; }
            h2 { font-size: 13px; margin-top: 20px; }
            .meta { color: #666; font-size: 10px; }
        </style>
        <h1>Ticket: {$ticket->numero_ticket}</h1>
        <p class='meta'>{$empresa->razon_social} | RUC: {$empresa->ruc}</p>
        <p><strong>Asunto:</strong> {$ticket->asunto}</p>
        <p><strong>Estado:</strong> {$ticket->estado} | <strong>Prioridad:</strong> {$ticket->prioridad} | <strong>Categoría:</strong> {$ticket->categoria}</p>
        <p><strong>Descripción:</strong></p>
        <p>{$ticket->descripcion}</p>
        <h2>Conversación</h2>
        {$mensajesHtml}";

        $mpdf = new Mpdf([
            'format' => 'A4',
            'tempDir' => storage_path('app/temp'),
        ]);
        $mpdf->WriteHTML($html);

        $path = storage_path("app/temp/ticket_{$ticket->numero_ticket}.pdf");
        $mpdf->Output($path, 'F');

        return $path;
    }
}
