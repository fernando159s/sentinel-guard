<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupEjecucion extends Model
{
    protected $table = 'backup_ejecuciones';

    protected $fillable = [
        'programacion_id',
        'fecha_ejecucion',
        'ejecutado_por',
        'estado',
        'notas',
        'registro_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ejecucion' => 'datetime',
        ];
    }

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(BackupProgramacion::class, 'programacion_id');
    }

    public function ejecutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ejecutado_por')->withoutGlobalScopes();
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }
}
