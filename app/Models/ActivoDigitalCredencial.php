<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoDigitalCredencial extends Model
{
    protected $table = 'activo_digital_credenciales';

    protected $fillable = [
        'activo_digital_id',
        'etiqueta',
        'usuario',
        'password',
        'dato_2fa',
        'recovery',
        'notas',
    ];

    /**
     * Los campos sensibles se cifran en reposo con la APP_KEY (cast 'encrypted').
     * Nunca exponer en listados, logs ni reportes.
     */
    protected function casts(): array
    {
        return [
            'usuario' => 'encrypted',
            'password' => 'encrypted',
            'dato_2fa' => 'encrypted',
            'recovery' => 'encrypted',
            'notas' => 'encrypted',
        ];
    }

    protected $hidden = [
        'usuario',
        'password',
        'dato_2fa',
        'recovery',
        'notas',
    ];

    public function activoDigital(): BelongsTo
    {
        return $this->belongsTo(ActivoDigital::class, 'activo_digital_id');
    }

    /**
     * Limita la consulta a las credenciales que un usuario puede ver en el
     * Baul de Contrasenas. Refleja exactamente ActivoDigitalCredencialPolicy::view():
     *
     * - El filtro por empresa (tenant) es explicito y siempre aplica, asi que
     *   ni siquiera un super_admin cruza datos entre empresas en esta vista.
     * - Los roles de gestion (super_admin, admin_empresa) ven todas las cuentas
     *   de la empresa; el resto solo las cuentas de las que son responsables.
     * - El whereHas sobre activoDigital arrastra los global scopes del activo
     *   (EmpresaScope + SoftDeletes), por lo que no aparecen credenciales de
     *   cuentas eliminadas ni de otras empresas.
     */
    public function scopeVisiblesPara(Builder $query, User $user, ?int $empresaId): Builder
    {
        return $query->whereHas('activoDigital', function (Builder $q) use ($user, $empresaId): void {
            if ($empresaId !== null) {
                $q->where('activos_digitales.empresa_id', $empresaId);
            }

            if (! $user->hasRole(['super_admin', 'admin_empresa'])) {
                $q->whereHas('responsables', fn (Builder $r) => $r->whereKey($user->id));
            }
        });
    }
}
