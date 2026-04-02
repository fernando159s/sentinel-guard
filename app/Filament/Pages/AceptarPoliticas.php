<?php

namespace App\Filament\Pages;

use App\Models\AceptacionPolitica;
use App\Models\Politica;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AceptarPoliticas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Aceptar politicas';

    protected static ?string $slug = 'aceptar-politicas';

    protected string $view = 'filament.pages.aceptar-politicas';

    public ?Politica $politicaActual = null;

    public bool $acepto = false;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->empresa_id) {
            $this->redirect('/admin');

            return;
        }

        $pendientes = Politica::pendientesPara($user->id, $user->empresa_id);

        if ($pendientes->isEmpty()) {
            $this->redirect('/admin/' . ($user->empresa?->ruc ?? ''));

            return;
        }

        $this->politicaActual = $pendientes->first();
    }

    public function aceptar(): void
    {
        if (! $this->acepto) {
            Notification::make()
                ->title('Debes marcar la casilla para aceptar')
                ->warning()
                ->send();

            return;
        }

        AceptacionPolitica::create([
            'user_id' => auth()->id(),
            'politica_id' => $this->politicaActual->id,
            'version_aceptada' => $this->politicaActual->version,
            'fecha_aceptacion' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->acepto = false;

        // Check if more pending
        $user = auth()->user();
        $pendientes = Politica::pendientesPara($user->id, $user->empresa_id);

        if ($pendientes->isNotEmpty()) {
            $this->politicaActual = $pendientes->first();

            Notification::make()
                ->title('Politica aceptada')
                ->body('Aun tienes politicas pendientes por aceptar.')
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title('Todas las politicas aceptadas')
                ->success()
                ->send();

            $this->redirect('/admin/' . ($user->empresa?->ruc ?? ''));
        }
    }
}
