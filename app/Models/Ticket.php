<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'empresa_id',
        'numero_ticket',
        'creado_por',
        'asignado_a',
        'asunto',
        'descripcion',
        'categoria',
        'prioridad',
        'estado',
        'fecha_ultima_actividad',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ultima_actividad' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMensaje::class, 'ticket_id');
    }
}
