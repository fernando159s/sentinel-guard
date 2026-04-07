<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AceptacionPolitica extends Model
{
    protected $table = 'aceptaciones_politica';

    protected $fillable = [
        'user_id',
        'politica_id',
        'version_aceptada',
        'fecha_aceptacion',
        'ip_address',
        'user_agent',
        'firma_imagen',
        'firma_nombre',
        'firma_cargo',
        'fecha_expiracion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_aceptacion' => 'datetime',
            'fecha_expiracion' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function politica(): BelongsTo
    {
        return $this->belongsTo(Politica::class, 'politica_id');
    }

    public function estaVigente(): bool
    {
        return $this->fecha_expiracion === null || $this->fecha_expiracion->isFuture();
    }
}
