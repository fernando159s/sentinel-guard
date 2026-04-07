<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliticaVersion extends Model
{
    protected $table = 'politica_versiones';

    protected $fillable = [
        'politica_id',
        'version',
        'contenido',
        'archivo_path',
        'archivo_nombre',
        'creado_por',
    ];

    public function politica(): BelongsTo
    {
        return $this->belongsTo(Politica::class, 'politica_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
