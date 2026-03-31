<?php

namespace App\Services;

use App\Enums\TipoFormato;
use Illuminate\Support\Facades\DB;

class RegistroNumberService
{
    /**
     * Generate the next registro number: PREFIJO-AÑO-SEQ (e.g. INC-2026-004)
     * Atomic via SELECT ... FOR UPDATE on secuencias_registro.
     */
    public static function generate(int $empresaId, TipoFormato $tipo): string
    {
        $prefix = $tipo->prefix();
        $year = now()->year;

        return DB::transaction(function () use ($empresaId, $tipo, $prefix, $year) {
            $seq = DB::table('secuencias_registro')
                ->where('empresa_id', $empresaId)
                ->where('tipo_formato', $tipo->value)
                ->where('anio', $year)
                ->lockForUpdate()
                ->first();

            if ($seq) {
                $next = $seq->ultimo_seq + 1;
                DB::table('secuencias_registro')
                    ->where('empresa_id', $empresaId)
                    ->where('tipo_formato', $tipo->value)
                    ->where('anio', $year)
                    ->update(['ultimo_seq' => $next]);
            } else {
                $next = 1;
                DB::table('secuencias_registro')->insert([
                    'empresa_id' => $empresaId,
                    'tipo_formato' => $tipo->value,
                    'anio' => $year,
                    'ultimo_seq' => $next,
                ]);
            }

            return sprintf('%s-%d-%03d', $prefix, $year, $next);
        });
    }
}
