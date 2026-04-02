<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistPlantilla extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $table = 'checklist_plantillas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'items',
        'periodicidad',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'activa' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function ejecuciones(): HasMany
    {
        return $this->hasMany(ChecklistEjecucion::class, 'checklist_plantilla_id');
    }

    public function diasPeriodicidad(): int
    {
        return match ($this->periodicidad) {
            'semanal' => 7,
            'mensual' => 30,
            'trimestral' => 90,
            'semestral' => 180,
            'anual' => 365,
            'unica' => 99999,
            default => 30,
        };
    }
}
