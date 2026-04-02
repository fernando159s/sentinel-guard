<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Politica extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'titulo',
        'slug',
        'contenido',
        'version',
        'obligatoria',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Politica $politica) {
            if (empty($politica->slug)) {
                $politica->slug = Str::slug($politica->titulo);
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function aceptaciones(): HasMany
    {
        return $this->hasMany(AceptacionPolitica::class, 'politica_id');
    }

    /**
     * Get obligatory active policies that a user hasn't accepted (or accepted an older version).
     */
    public static function pendientesPara(int $userId, int $empresaId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('empresa_id', $empresaId)
            ->where('obligatoria', true)
            ->where('activa', true)
            ->whereDoesntHave('aceptaciones', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->whereColumn('aceptaciones_politica.version_aceptada', 'politicas.version');
            })
            ->get();
    }

    public function aceptadaPor(int $userId): bool
    {
        return $this->aceptaciones()
            ->where('user_id', $userId)
            ->where('version_aceptada', $this->version)
            ->exists();
    }
}
