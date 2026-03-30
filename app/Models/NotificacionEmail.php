<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionEmail extends Model
{
    public $timestamps = false;

    protected $table = 'notificaciones_email';

    protected $fillable = [
        'destinatario',
        'nombre_destino',
        'asunto',
        'cuerpo_html',
        'estado',
        'intentos',
        'error_mensaje',
        'fecha_programada',
        'fecha_envio',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'datetime',
            'fecha_envio' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
