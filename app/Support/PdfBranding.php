<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class PdfBranding
{
    public const DEFAULT_PRIMARIO = '#4f46e5';

    public const DEFAULT_SECUNDARIO = '#1e1b4b';

    public static function colors(?Empresa $empresa): array
    {
        return [
            'primario' => $empresa?->getPdfColorPrimario() ?? self::DEFAULT_PRIMARIO,
            'secundario' => $empresa?->getPdfColorSecundario() ?? self::DEFAULT_SECUNDARIO,
        ];
    }

    public static function attachLogo(Mpdf $mpdf, ?Empresa $empresa): bool
    {
        $logoPath = $empresa?->getPdfLogoPath();
        if (! $logoPath) {
            return false;
        }

        $disk = Storage::disk('logos');
        if (! $disk->exists($logoPath)) {
            return false;
        }

        $mpdf->imageVars['logo'] = $disk->get($logoPath);

        return true;
    }

    public static function logoHtml(bool $hasLogo, int $height = 42): string
    {
        return $hasLogo
            ? '<img src="var:logo" style="height:'.$height.'px;margin-bottom:4px;" /><br>'
            : '';
    }

    /**
     * Compact base style for tabular PDF reports.
     */
    public static function reportStyle(?Empresa $empresa): string
    {
        $c = self::colors($empresa);

        return '
        <style>
            body { font-family: Arial, sans-serif; font-size: 9px; color: #1f2937; line-height: 1.35; }
            h1 { color: '.$c['primario'].'; font-size: 14px; margin: 0 0 2px; }
            h2 { color: '.$c['secundario'].'; font-size: 11px; margin: 12px 0 4px; border-bottom: 1px solid '.$c['primario'].'; padding-bottom: 2px; }
            h3 { font-size: 10px; margin: 8px 0 3px; color: #374151; }
            p { margin: 2px 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 4px; }
            th, td { border: 1px solid #e5e7eb; padding: 3px 5px; text-align: left; vertical-align: top; }
            th { background: #f3f4f6; font-weight: bold; font-size: 8.5px; color: '.$c['secundario'].'; }
            .meta { color: #6b7280; font-size: 8.5px; margin: 0 0 6px; }
            .summary { padding: 6px 8px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 3px; margin: 6px 0; font-size: 9px; }
            .footer { margin-top: 14px; font-size: 7.5px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }
            .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
            .badge-alta { background: #fee2e2; color: #b91c1c; }
            .badge-media { background: #fef3c7; color: #b45309; }
            .badge-baja { background: #d1fae5; color: #047857; }
            .ficha { border: 1px solid #e5e7eb; border-radius: 4px; margin-top: 8px; }
            .ficha-header { background: '.$c['primario'].'14; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
            .ficha-header h2 { border: none; margin: 0; padding: 0; color: '.$c['secundario'].'; }
            .ficha-body { padding: 8px 10px; }
            .field { margin-bottom: 5px; }
            .field-label { font-size: 7.5px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }
            .field-value { font-size: 9px; color: #111827; }
            .field-value-long { font-size: 8.5px; color: #111827; padding: 4px 6px; background: #fafafa; border: 1px solid #f3f4f6; border-radius: 2px; }
            .grid-2, .grid-3 { display: flex; }
            .grid-2 > div { width: 50%; }
            .grid-3 > div { width: 33.33%; }
        </style>';
    }
}
