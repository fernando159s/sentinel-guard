<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipoAsignacion extends Model
{
    protected $table = 'equipo_asignaciones';

    protected $fillable = [
        'equipo_id',
        'user_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'condicion_entrega',
        'condicion_devolucion',
        'notas',
        'asignado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por')->withoutGlobalScopes();
    }

    public function esVigente(): bool
    {
        return $this->fecha_fin === null && in_array($this->tipo, ['asignacion', 'transferencia']);
    }
}
