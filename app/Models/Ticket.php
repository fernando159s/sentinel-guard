<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use BelongsToEmpresa;
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
        return $this->belongsTo(User::class, 'creado_por')->withoutGlobalScopes();
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a')->withoutGlobalScopes();
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMensaje::class, 'ticket_id');
    }

    public function equipos(): BelongsToMany
    {
        return $this->belongsToMany(Equipo::class, 'ticket_equipo');
    }

    public function puedeReabrirse(): bool
    {
        if ($this->estado !== 'resuelto') {
            return false;
        }

        if (! $this->fecha_cierre) {
            return false;
        }

        return $this->fecha_cierre->greaterThan(now()->subDays(7));
    }
}
