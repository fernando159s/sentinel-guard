<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAdjunto extends Model
{
    public $timestamps = false;

    protected $table = 'ticket_adjuntos';

    protected $fillable = [
        'mensaje_id',
        'nombre_original',
        'nombre_almacenado',
        'tipo_mime',
        'tamano',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(TicketMensaje::class, 'mensaje_id');
    }
}
