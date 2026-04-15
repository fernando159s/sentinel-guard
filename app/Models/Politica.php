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
        'archivo_path',
        'archivo_nombre',
        'version',
        'obligatoria',
        'activa',
        'es_nda',
        'vigencia_meses',
        'firma_admin_imagen',
        'firma_admin_nombre',
        'firma_admin_cargo',
        'firmado_por',
        'fecha_firma_admin',
    ];

    protected function casts(): array
    {
        return [
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
            'es_nda' => 'boolean',
            'vigencia_meses' => 'integer',
            'fecha_firma_admin' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Politica $politica) {
            if (empty($politica->slug)) {
                $politica->slug = Str::slug($politica->titulo);
            }
        });

        static::updating(function (Politica $politica) {
            if ($politica->isDirty('version')) {
                PoliticaVersion::create([
                    'politica_id' => $politica->id,
                    'version' => $politica->getOriginal('version'),
                    'contenido' => $politica->getOriginal('contenido'),
                    'archivo_path' => $politica->getOriginal('archivo_path'),
                    'archivo_nombre' => $politica->getOriginal('archivo_nombre'),
                    'creado_por' => auth()->id(),
                ]);
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function firmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'firmado_por')->withoutGlobalScopes();
    }

    public function estaFirmadaPorAdmin(): bool
    {
        return $this->firma_admin_imagen !== null && $this->firmado_por !== null;
    }

    public function aceptaciones(): HasMany
    {
        return $this->hasMany(AceptacionPolitica::class, 'politica_id');
    }

    public function versiones(): HasMany
    {
        return $this->hasMany(PoliticaVersion::class, 'politica_id')->orderByDesc('created_at');
    }

    public function scopeNdas($query)
    {
        return $query->where('es_nda', true);
    }

    /**
     * Get obligatory active policies that a user hasn't accepted (or accepted an older version),
     * including NDAs whose acceptance has expired.
     */
    public static function pendientesPara(int $userId, int $empresaId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('empresa_id', $empresaId)
            ->where('obligatoria', true)
            ->where('activa', true)
            ->where(function ($query) use ($userId) {
                // Regular policies: not accepted at current version
                $query->whereDoesntHave('aceptaciones', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->whereColumn('aceptaciones_politica.version_aceptada', 'politicas.version');
                })
                // OR NDAs with expired acceptance
                ->orWhere(function ($q) use ($userId) {
                    $q->where('es_nda', true)
                      ->whereDoesntHave('aceptaciones', function ($sub) use ($userId) {
                          $sub->where('user_id', $userId)
                              ->whereColumn('aceptaciones_politica.version_aceptada', 'politicas.version')
                              ->where(function ($vigencia) {
                                  $vigencia->whereNull('fecha_expiracion')
                                           ->orWhere('fecha_expiracion', '>', now());
                              });
                      });
                });
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
