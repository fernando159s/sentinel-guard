<?php

namespace App\Services;

use App\Enums\TipoFormato;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public static function exportRegistros(Collection $registros, ?string $empresaName = null): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Registros');

        // Header
        $row = 1;
        if ($empresaName) {
            $sheet->setCellValue("A{$row}", $empresaName);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row++;
            $sheet->setCellValue("A{$row}", 'Exportado: ' . now()->format('d/m/Y H:i'));
            $row += 2;
        }

        // Column headers
        $headers = ['N° Registro', 'Formato', 'Estado', 'Creado por', 'Fecha creación'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        $row++;

        // Data
        foreach ($registros as $registro) {
            $tipo = TipoFormato::tryFrom($registro->tipo_formato);
            $sheet->setCellValue("A{$row}", $registro->numero_registro);
            $sheet->setCellValue("B{$row}", $tipo?->label() ?? $registro->tipo_formato);
            $sheet->setCellValue("C{$row}", $registro->estado);
            $sheet->setCellValue("D{$row}", $registro->creador?->name ?? '-');
            $sheet->setCellValue("E{$row}", $registro->created_at?->format('d/m/Y H:i'));
            $row++;
        }

        // Auto-width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'registros_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path("app/temp/{$filename}");

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
