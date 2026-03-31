<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model implements HasName
{
    protected $fillable = [
        'ruc',
        'razon_social',
        'logo_path',
        'direccion',
        'email',
        'telefono',
        'estado',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class, 'empresa_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'empresa_id');
    }

    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function getFilamentName(): string
    {
        return $this->razon_social;
    }
}
