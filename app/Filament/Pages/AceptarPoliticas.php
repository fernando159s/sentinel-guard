<?php

namespace App\Filament\Pages;

use App\Models\AceptacionPolitica;
use App\Models\Politica;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class AceptarPoliticas extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    protected static ?string $slug = 'aceptar-politicas';

    protected string $view = 'filament.pages.aceptar-politicas';

    public ?Politica $politicaActual = null;

    public bool $acepto = false;

    public string $firmaDataUrl = '';

    public string $firmaNombre = '';

    public string $firmaCargo = '';

    public $firmaUpload = null;

    public string $metodoFirma = 'dibujar'; // dibujar | subir

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
        $this->firmaNombre = $user->name;

        // If user has saved signature, pre-fill
        if ($user->firma_guardada) {
            $this->firmaDataUrl = $user->firma_guardada;
        }
    }

    public function aceptar(): void
    {
        if (! $this->acepto) {
            Notification::make()->title('Debes marcar la casilla')->warning()->send();

            return;
        }

        // Get firma image
        $firmaImagen = $this->firmaDataUrl;

        if ($this->metodoFirma === 'subir' && $this->firmaUpload) {
            $firmaImagen = 'data:image/png;base64,' . base64_encode(file_get_contents($this->firmaUpload->getRealPath()));
        }

        if (empty($firmaImagen)) {
            Notification::make()->title('Debes firmar el documento')->warning()->send();

            return;
        }

        if (empty($this->firmaNombre)) {
            Notification::make()->title('Ingresa tu nombre completo')->warning()->send();

            return;
        }

        AceptacionPolitica::create([
            'user_id' => auth()->id(),
            'politica_id' => $this->politicaActual->id,
            'version_aceptada' => $this->politicaActual->version,
            'fecha_aceptacion' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'firma_imagen' => $firmaImagen,
            'firma_nombre' => $this->firmaNombre,
            'firma_cargo' => $this->firmaCargo,
        ]);

        // Save signature for reuse
        $user = auth()->user();
        if (! $user->firma_guardada) {
            $user->update(['firma_guardada' => $firmaImagen]);
        }

        $this->acepto = false;
        $this->firmaDataUrl = '';
        $this->firmaUpload = null;
        $this->firmaCargo = '';
        $this->dispatch('firma-cleared');

        $pendientes = Politica::pendientesPara($user->id, $user->empresa_id);

        if ($pendientes->isNotEmpty()) {
            $this->politicaActual = $pendientes->first();
            Notification::make()->title('Politica aceptada y firmada')->body('Aun tienes politicas pendientes.')->info()->send();
        } else {
            Notification::make()->title('Todas las politicas firmadas')->success()->send();
            $this->redirect('/admin/' . ($user->empresa?->ruc ?? ''));
        }
    }

    public function limpiarFirma(): void
    {
        $this->firmaDataUrl = '';
    }
}
