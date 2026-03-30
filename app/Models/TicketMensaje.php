<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMensaje extends Model
{
    public $timestamps = false;

    protected $table = 'ticket_mensajes';

    protected $fillable = [
        'ticket_id',
        'autor_id',
        'tipo',
        'contenido',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketAdjunto::class, 'mensaje_id');
    }

    public function isInterno(): bool
    {
        return $this->tipo === 'interno';
    }
}
