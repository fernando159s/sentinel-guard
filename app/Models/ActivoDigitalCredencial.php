<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoDigitalCredencial extends Model
{
    protected $table = 'activo_digital_credenciales';

    protected $fillable = [
        'activo_digital_id',
        'etiqueta',
        'usuario',
        'password',
        'dato_2fa',
        'recovery',
        'notas',
    ];

    /**
     * Los campos sensibles se cifran en reposo con la APP_KEY (cast 'encrypted').
     * Nunca exponer en listados, logs ni reportes.
     */
    protected function casts(): array
    {
        return [
            'usuario' => 'encrypted',
            'password' => 'encrypted',
            'dato_2fa' => 'encrypted',
            'recovery' => 'encrypted',
            'notas' => 'encrypted',
        ];
    }

    protected $hidden = [
        'usuario',
        'password',
        'dato_2fa',
        'recovery',
        'notas',
    ];

    public function activoDigital(): BelongsTo
    {
        return $this->belongsTo(ActivoDigital::class, 'activo_digital_id');
    }
}
