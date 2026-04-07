<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupProgramacion extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $table = 'backup_programaciones';

    protected $fillable = [
        'empresa_id',
        'equipo_id',
        'nombre',
        'periodicidad',
        'proximo_backup',
        'descripcion',
        'activo',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'proximo_backup' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function ejecuciones(): HasMany
    {
        return $this->hasMany(BackupEjecucion::class, 'programacion_id')->orderByDesc('fecha_ejecucion');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por')->withoutGlobalScopes();
    }

    public function calcularProximoBackup(): string
    {
        $fecha = $this->proximo_backup->copy();

        return match ($this->periodicidad) {
            'diaria' => $fecha->addDay()->toDateString(),
            'semanal' => $fecha->addWeek()->toDateString(),
            'quincenal' => $fecha->addWeeks(2)->toDateString(),
            'mensual' => $fecha->addMonth()->toDateString(),
            'trimestral' => $fecha->addMonths(3)->toDateString(),
            'puntual' => $fecha->toDateString(), // no avanza
            default => $fecha->addMonth()->toDateString(),
        };
    }

    public function estaAtrasado(): bool
    {
        return $this->activo && $this->proximo_backup->isPast();
    }

    public function estaProximo(): bool
    {
        return $this->activo && $this->proximo_backup->isToday() || $this->proximo_backup->isTomorrow();
    }
}
