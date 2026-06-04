<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registro extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'tipo_formato',
        'numero_registro',
        'datos',
        'estado',
        'creado_por',
        'modificado_por',
        'equipo_id',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
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

    public function modificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por')->withoutGlobalScopes();
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function activosDigitales(): HasMany
    {
        return $this->hasMany(ActivoDigital::class, 'registro_id');
    }
}
