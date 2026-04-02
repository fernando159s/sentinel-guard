<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistEjecucion extends Model
{
    protected $table = 'checklist_ejecuciones';

    protected $fillable = [
        'checklist_plantilla_id',
        'equipo_id',
        'ejecutado_por',
        'fecha_ejecucion',
        'resultados',
        'estado',
        'observaciones_generales',
    ];

    protected function casts(): array
    {
        return [
            'resultados' => 'array',
            'fecha_ejecucion' => 'datetime',
        ];
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(ChecklistPlantilla::class, 'checklist_plantilla_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function ejecutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ejecutado_por')->withoutGlobalScopes();
    }

    public function itemsCumplen(): int
    {
        return collect($this->resultados ?? [])->where('cumple', true)->count();
    }

    public function totalItems(): int
    {
        return count($this->resultados ?? []);
    }
}
