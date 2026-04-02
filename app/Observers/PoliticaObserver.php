<?php

namespace App\Observers;

use App\Jobs\SendEmailJob;
use App\Models\Politica;
use App\Models\User;

class PoliticaObserver
{
    public function created(Politica $politica): void
    {
        if ($politica->activa && $politica->obligatoria) {
            $this->notifyUsers($politica);
        }
    }

    public function updated(Politica $politica): void
    {
        if ($politica->wasChanged('version') && $politica->activa && $politica->obligatoria) {
            $this->notifyUsers($politica);
        }
    }

    private function notifyUsers(Politica $politica): void
    {
        if (! $politica->empresa_id) {
            return;
        }

        $users = User::where('empresa_id', $politica->empresa_id)
            ->where('estado', 'activo')
            ->get();

        $loginUrl = url('admin/' . ($politica->empresa?->ruc ?? '') . '/aceptar-politicas');

        foreach ($users as $user) {
            dispatch(SendEmailJob::fromTemplate(
                destinatario: $user->email,
                nombreDestino: $user->name,
                templateSlug: 'nueva_politica',
                templateVariables: [
                    'nombre' => $user->name,
                    'titulo_politica' => $politica->titulo,
                    'version' => $politica->version,
                    'enlace_plataforma' => $loginUrl,
                ],
                actionUrl: $loginUrl,
                actionLabel: 'Aceptar politica',
            ));
        }
    }
}
