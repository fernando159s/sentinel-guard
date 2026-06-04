<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'tipo',
        'marca',
        'modelo',
        'numero_serie',
        'codigo_interno',
        'sistema_operativo',
        'procesador',
        'ram_gb',
        'disco_gb',
        'estado',
        'ubicacion',
        'nivel_sensibilidad',
        'fecha_adquisicion',
        'fecha_garantia',
        'observaciones',
        'categoria',
        'contenido_datos',
        'clasificacion_soporte',
    ];

    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_garantia' => 'date',
            'ram_gb' => 'integer',
            'disco_gb' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(EquipoAsignacion::class, 'equipo_id');
    }

    public function asignacionVigente(): HasOne
    {
        return $this->hasOne(EquipoAsignacion::class, 'equipo_id')
            ->whereNull('fecha_fin')
            ->whereIn('tipo', ['asignacion', 'transferencia'])
            ->latest('fecha_inicio');
    }

    public function usuarioActual(): ?User
    {
        return $this->asignacionVigente?->user;
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_equipo');
    }

    public function checklistEjecuciones(): HasMany
    {
        return $this->hasMany(ChecklistEjecucion::class, 'equipo_id');
    }

    public function estaAsignado(): bool
    {
        return $this->asignacionVigente()->exists();
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'activo' && ! $this->estaAsignado();
    }

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class, 'equipo_id');
    }

    public function backupProgramaciones(): HasMany
    {
        return $this->hasMany(BackupProgramacion::class, 'equipo_id');
    }

    public function activosDigitales(): HasMany
    {
        return $this->hasMany(ActivoDigital::class, 'equipo_id');
    }

    public function esTecnologico(): bool
    {
        return $this->categoria === 'tecnologico';
    }
}
