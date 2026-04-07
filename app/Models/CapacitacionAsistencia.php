<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacitacionAsistencia extends Model
{
    protected $table = 'capacitacion_asistencias';

    protected $fillable = [
        'capacitacion_id',
        'user_id',
        'asistio',
        'confirmado_por',
        'fecha_confirmacion',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
            'fecha_confirmacion' => 'datetime',
        ];
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'capacitacion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }
}
