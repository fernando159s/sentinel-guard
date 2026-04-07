<?php

namespace App\Filament\Pages;

use App\Models\Politica;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CompletarDatosNda extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Completar datos personales';

    protected static ?string $slug = 'completar-datos-nda';

    protected string $view = 'filament.pages.completar-datos-nda';

    public string $dni = '';

    public string $direccion = '';

    public string $telefono = '';

    public string $puesto = '';

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->empresa_id) {
            $this->redirect('/admin');

            return;
        }

        // Pre-fill with existing data
        $this->dni = $user->dni ?? '';
        $this->direccion = $user->direccion ?? '';
        $this->telefono = $user->telefono ?? '';
        $this->puesto = $user->puesto ?? '';

        // If user already has all data, skip to acceptance
        if ($this->datosCompletos()) {
            $ruc = $user->empresa?->ruc ?? '';
            $this->redirect("/admin/{$ruc}/aceptar-politicas");

            return;
        }
    }

    public function guardar(): void
    {
        $this->validate([
            'dni' => 'required|string|max:20',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:30',
            'puesto' => 'required|string|max:255',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'direccion.required' => 'La direccion es obligatoria.',
            'telefono.required' => 'El telefono es obligatorio.',
            'puesto.required' => 'El puesto es obligatorio.',
        ]);

        $user = auth()->user();
        $user->update([
            'dni' => $this->dni,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'puesto' => $this->puesto,
        ]);

        Notification::make()
            ->title('Datos guardados correctamente')
            ->success()
            ->send();

        $ruc = $user->empresa?->ruc ?? '';
        $this->redirect("/admin/{$ruc}/aceptar-politicas");
    }

    private function datosCompletos(): bool
    {
        $user = auth()->user();

        return filled($user->dni)
            && filled($user->direccion)
            && filled($user->telefono)
            && filled($user->puesto);
    }

    public function getNdaPendiente(): ?Politica
    {
        $user = auth()->user();

        return Politica::where('empresa_id', $user->empresa_id)
            ->where('es_nda', true)
            ->where('obligatoria', true)
            ->where('activa', true)
            ->whereDoesntHave('aceptaciones', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereColumn('aceptaciones_politica.version_aceptada', 'politicas.version')
                    ->where(function ($v) {
                        $v->whereNull('fecha_expiracion')
                            ->orWhere('fecha_expiracion', '>', now());
                    });
            })
            ->first();
    }
}
