<?php

namespace App\Models;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivoDigital extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $table = 'activos_digitales';

    protected $fillable = [
        'empresa_id',
        'codigo_interno',
        'nombre',
        'tipo',
        'proveedor',
        'url',
        'identificador',
        'estado',
        'nivel_sensibilidad',
        'responsable_id',
        'modalidad_pago',
        'costo',
        'moneda',
        'metodo_pago',
        'renovacion_automatica',
        'fecha_adquisicion',
        'fecha_vencimiento',
        'equipo_id',
        'registro_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoActivoDigital::class,
            'estado' => EstadoActivoDigital::class,
            'modalidad_pago' => ModalidadPago::class,
            'costo' => 'decimal:2',
            'renovacion_automatica' => 'boolean',
            'fecha_adquisicion' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ActivoDigital $activo) {
            if (empty($activo->codigo_interno)) {
                $activo->codigo_interno = static::generarCodigoInterno($activo->empresa_id);
            }
        });
    }

    /** Genera el siguiente codigo correlativo AD-XXX por empresa. */
    public static function generarCodigoInterno(?int $empresaId): string
    {
        $ultimo = static::withTrashed()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('codigo_interno', 'like', 'AD-%')
            ->orderByDesc('id')
            ->value('codigo_interno');

        $secuencia = $ultimo ? ((int) substr($ultimo, 3)) + 1 : 1;

        return 'AD-'.str_pad((string) $secuencia, 3, '0', STR_PAD_LEFT);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Lista de responsables con acceso a las credenciales de esta cuenta.
     */
    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'activo_digital_responsables')
            ->withTimestamps();
    }

    /** ¿El usuario indicado es responsable de esta cuenta? */
    public function esResponsable(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->responsables()->whereKey($user->id)->exists();
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    public function credenciales(): HasMany
    {
        return $this->hasMany(ActivoDigitalCredencial::class, 'activo_digital_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(ActivoDigitalPago::class, 'activo_digital_id')->latest('fecha_pago');
    }

    /** ¿Está por vencer dentro de los próximos $dias días? */
    public function porVencer(int $dias = 30): bool
    {
        if (! $this->fecha_vencimiento || ! $this->modalidad_pago?->esRecurrente()) {
            return false;
        }

        return $this->fecha_vencimiento->isFuture()
            && $this->fecha_vencimiento->lessThanOrEqualTo(now()->addDays($dias));
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento
            && $this->modalidad_pago?->esRecurrente()
            && $this->fecha_vencimiento->isPast();
    }
}
