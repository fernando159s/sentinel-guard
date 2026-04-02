<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'empresa_id',
        'rol',
        'estado',
        'intentos_fallidos',
        'bloqueado_hasta',
        'notif_tickets',
        'notif_incidencias',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'bloqueado_hasta' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'notif_tickets' => 'boolean',
            'notif_incidencias' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function registrosCreados(): HasMany
    {
        return $this->hasMany(Registro::class, 'creado_por');
    }

    public function ticketsCreados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'creado_por');
    }

    public function ticketsAsignados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'asignado_a');
    }

    public function equipoAsignaciones(): HasMany
    {
        return $this->hasMany(EquipoAsignacion::class, 'user_id');
    }

    public function equiposAsignados(): HasMany
    {
        return $this->hasMany(EquipoAsignacion::class, 'user_id')
            ->whereNull('fecha_fin')
            ->whereIn('tipo', ['asignacion', 'transferencia']);
    }

    public function aceptacionesPolitica(): HasMany
    {
        return $this->hasMany(AceptacionPolitica::class, 'user_id');
    }

    public function tienePoliticasPendientes(): bool
    {
        if (! $this->empresa_id) {
            return false;
        }

        return Politica::pendientesPara($this->id, $this->empresa_id)->isNotEmpty();
    }

    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActivo();
    }

    public function getTenants(Panel $panel): Collection
    {
        // Super admin and agents can see all companies
        if ($this->hasRole(['super_admin', 'agente_helpdesk'])) {
            return Empresa::where('estado', 'activo')->get();
        }

        // Regular users only see their own company
        if ($this->empresa_id) {
            return Empresa::where('id', $this->empresa_id)->get();
        }

        return collect();
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        if ($this->hasRole(['super_admin', 'agente_helpdesk'])) {
            return true;
        }

        return $this->empresa_id === $tenant->getKey();
    }
}
