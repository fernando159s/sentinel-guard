<?php

namespace App\Models;

use App\Enums\ModalidadCapacitacion;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Capacitacion extends Model
{
    use BelongsToEmpresa, SoftDeletes;

    protected $table = 'capacitaciones';

    protected $fillable = [
        'empresa_id',
        'tema',
        'descripcion',
        'fecha',
        'hora_inicio',
        'duracion_minutos',
        'modalidad',
        'expositor',
        'registro_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'modalidad' => ModalidadCapacitacion::class,
            'duracion_minutos' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(CapacitacionAsistencia::class, 'capacitacion_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fechaHoraFin(): Carbon
    {
        return $this->fecha
            ->copy()
            ->setTimeFromTimeString($this->hora_inicio)
            ->addMinutes($this->duracion_minutos);
    }

    public function yaTermino(): bool
    {
        return now()->greaterThan($this->fechaHoraFin());
    }

    public function dentroVentanaConfirmacion(): bool
    {
        $fin = $this->fechaHoraFin();

        return now()->greaterThan($fin) && now()->lessThanOrEqualTo($fin->copy()->addHours(24));
    }

    public function porcentajeAsistencia(): float
    {
        $total = $this->asistencias()->count();

        if ($total === 0) {
            return 0;
        }

        $asistieron = $this->asistencias()->where('asistio', true)->count();

        return round(($asistieron / $total) * 100, 1);
    }
}
