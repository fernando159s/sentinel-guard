<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoDigitalPago extends Model
{
    protected $table = 'activo_digital_pagos';

    protected $fillable = [
        'activo_digital_id',
        'fecha_pago',
        'monto',
        'moneda',
        'metodo',
        'periodo_desde',
        'periodo_hasta',
        'comprobante',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
            'periodo_desde' => 'date',
            'periodo_hasta' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ActivoDigitalPago $pago) {
            if (! $pago->registrado_por && auth()->check()) {
                $pago->registrado_por = auth()->id();
            }
        });
    }

    public function activoDigital(): BelongsTo
    {
        return $this->belongsTo(ActivoDigital::class, 'activo_digital_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
