<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class MiPerfil extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'General';

    protected static ?string $navigationLabel = 'Mi perfil';

    protected static ?string $title = 'Mi perfil';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.mi-perfil';

    // Personal data
    public string $name = '';

    public string $email = '';

    public string $dni = '';

    public string $telefono = '';

    public string $direccion = '';

    public string $puesto = '';

    // Password
    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    // Notifications
    public bool $notif_tickets = true;

    public bool $notif_incidencias = true;

    // Signature
    public string $firmaDataUrl = '';

    public string $metodoFirma = 'dibujar';

    public $firmaUpload = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->dni = $user->dni ?? '';
        $this->telefono = $user->telefono ?? '';
        $this->direccion = $user->direccion ?? '';
        $this->puesto = $user->puesto ?? '';
        $this->notif_tickets = (bool) $user->notif_tickets;
        $this->notif_incidencias = (bool) $user->notif_incidencias;
        $this->firmaDataUrl = $user->firma_guardada ?? '';
    }

    public function guardarDatos(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'dni' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:500',
            'puesto' => 'nullable|string|max:150',
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
            'dni' => $this->dni,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'puesto' => $this->puesto,
        ]);

        Notification::make()->title('Datos actualizados')->success()->send();
    }

    public function cambiarPassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contrasena actual es incorrecta.',
            ]);
        }

        auth()->user()->update(['password' => $this->new_password]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        Notification::make()->title('Contrasena actualizada')->success()->send();
    }

    public function guardarNotificaciones(): void
    {
        auth()->user()->update([
            'notif_tickets' => $this->notif_tickets,
            'notif_incidencias' => $this->notif_incidencias,
        ]);

        Notification::make()->title('Preferencias guardadas')->success()->send();
    }

    public function guardarFirma(): void
    {
        $firmaImagen = $this->firmaDataUrl;

        if ($this->metodoFirma === 'subir' && $this->firmaUpload) {
            $mime = $this->firmaUpload->getMimeType();
            $base64 = base64_encode(file_get_contents($this->firmaUpload->getRealPath()));
            $firmaImagen = 'data:' . $mime . ';base64,' . $base64;
        }

        if (empty($firmaImagen)) {
            Notification::make()->title('Dibuja o sube una firma primero')->warning()->send();

            return;
        }

        auth()->user()->update(['firma_guardada' => $firmaImagen]);
        $this->firmaDataUrl = $firmaImagen;
        $this->firmaUpload = null;

        Notification::make()->title('Firma guardada')->success()->send();
    }

    public function eliminarFirma(): void
    {
        auth()->user()->update(['firma_guardada' => null]);
        $this->firmaDataUrl = '';
        $this->dispatch('firma-cleared');

        Notification::make()->title('Firma eliminada')->success()->send();
    }

    public function limpiarCanvas(): void
    {
        $this->firmaDataUrl = '';
        $this->dispatch('firma-cleared');
    }
}
