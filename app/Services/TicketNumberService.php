<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TicketNumberService
{
    /**
     * Generate the next ticket number: TKT-AÑO-SEQ (e.g. TKT-2026-004)
     * Atomic via SELECT ... FOR UPDATE on secuencias_ticket.
     */
    public static function generate(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $seq = DB::table('secuencias_ticket')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            $next = $seq->ultimo_seq + 1;

            DB::table('secuencias_ticket')
                ->where('id', 1)
                ->update(['ultimo_seq' => $next]);

            return sprintf('TKT-%d-%03d', $year, $next);
        });
    }
}
