<?php

namespace App\Filament\Pages;

use App\Jobs\SendEmailJob;
use App\Models\AceptacionPolitica;
use App\Models\Politica;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PanelCumplimiento extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Seguridad';

    protected static ?string $navigationLabel = 'Cumplimiento';

    protected static ?string $title = 'Panel de Cumplimiento';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.panel-cumplimiento';

    public ?int $selectedPoliticaId = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin_empresa'));
    }

    public function getStats(): array
    {
        $empresaId = Filament::getTenant()?->id;
        if (! $empresaId) {
            return ['total_politicas' => 0, 'cumplimiento_general' => 0, 'usuarios_pendientes' => 0];
        }

        $politicas = Politica::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->where('obligatoria', true)
            ->get();

        $totalPoliticas = $politicas->count();

        if ($totalPoliticas === 0) {
            return ['total_politicas' => 0, 'cumplimiento_general' => 100, 'usuarios_pendientes' => 0];
        }

        $usuarios = User::withoutGlobalScopes()
            ->firmantes()
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->get();

        $totalUsuarios = $usuarios->count();
        if ($totalUsuarios === 0) {
            return ['total_politicas' => $totalPoliticas, 'cumplimiento_general' => 100, 'usuarios_pendientes' => 0];
        }

        $totalPares = $totalPoliticas * $totalUsuarios;
        $aceptados = 0;
        $usuariosPendientes = collect();

        foreach ($politicas as $politica) {
            foreach ($usuarios as $usuario) {
                if ($politica->aceptadaPor($usuario->id)) {
                    $aceptados++;
                } else {
                    $usuariosPendientes->push($usuario->id);
                }
            }
        }

        return [
            'total_politicas' => $totalPoliticas,
            'cumplimiento_general' => $totalPares > 0 ? round(($aceptados / $totalPares) * 100) : 0,
            'usuarios_pendientes' => $usuariosPendientes->unique()->count(),
        ];
    }

    public function getPoliticas(): Collection
    {
        $empresaId = Filament::getTenant()?->id;
        if (! $empresaId) {
            return collect();
        }

        $politicas = Politica::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->where('obligatoria', true)
            ->orderBy('titulo')
            ->get();

        $totalUsuarios = User::withoutGlobalScopes()
            ->firmantes()
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->count();

        return $politicas->map(function ($politica) use ($totalUsuarios) {
            $aceptados = $politica->aceptaciones()
                ->where('version_aceptada', $politica->version)
                ->distinct('user_id')
                ->count('user_id');

            $porcentaje = $totalUsuarios > 0 ? round(($aceptados / $totalUsuarios) * 100) : 0;

            return (object) [
                'id' => $politica->id,
                'titulo' => $politica->titulo,
                'version' => $politica->version,
                'es_nda' => $politica->es_nda,
                'aceptados' => $aceptados,
                'total_usuarios' => $totalUsuarios,
                'porcentaje' => $porcentaje,
            ];
        });
    }

    public function getDetallePolitica(): ?Collection
    {
        if (! $this->selectedPoliticaId) {
            return null;
        }

        $empresaId = Filament::getTenant()?->id;
        if (! $empresaId) {
            return null;
        }

        $politica = Politica::withoutGlobalScopes()->find($this->selectedPoliticaId);
        if (! $politica) {
            return null;
        }

        $usuarios = User::withoutGlobalScopes()
            ->firmantes()
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('name')
            ->get();

        return $usuarios->map(function ($usuario) use ($politica) {
            $aceptacion = AceptacionPolitica::where('user_id', $usuario->id)
                ->where('politica_id', $politica->id)
                ->where('version_aceptada', $politica->version)
                ->latest('fecha_aceptacion')
                ->first();

            $estado = 'pendiente';
            if ($aceptacion) {
                if ($aceptacion->fecha_expiracion && $aceptacion->fecha_expiracion->isPast()) {
                    $estado = 'vencida';
                } else {
                    $estado = 'aceptada';
                }
            }

            return (object) [
                'user_id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'estado' => $estado,
                'fecha_aceptacion' => $aceptacion?->fecha_aceptacion?->format('d/m/Y H:i'),
                'fecha_expiracion' => $aceptacion?->fecha_expiracion?->format('d/m/Y'),
            ];
        });
    }

    public function selectPolitica(int $politicaId): void
    {
        $this->selectedPoliticaId = $politicaId;
    }

    public function enviarRecordatorio(int $userId): void
    {
        $user = User::withoutGlobalScopes()->find($userId);
        if (! $user) {
            return;
        }

        $loginUrl = url('admin/' . Filament::getTenant()?->ruc . '/login');

        dispatch(SendEmailJob::fromTemplate(
            destinatario: $user->email,
            nombreDestino: $user->name,
            templateSlug: 'recordatorio_politica',
            templateVariables: [
                'nombre' => $user->name,
                'enlace_login' => $loginUrl,
            ],
            saludo: "Hola {$user->name},",
            actionUrl: $loginUrl,
            actionLabel: 'Iniciar sesión',
        ));

        Notification::make()
            ->title('Recordatorio enviado')
            ->body("Se envió email a {$user->email}")
            ->success()
            ->send();
    }

    public function enviarRecordatorioMasivo(): void
    {
        $detalle = $this->getDetallePolitica();
        if (! $detalle) {
            return;
        }

        $pendientes = $detalle->filter(fn ($u) => $u->estado !== 'aceptada');
        $count = 0;

        foreach ($pendientes as $usuario) {
            $this->enviarRecordatorio($usuario->user_id);
            $count++;
        }

        Notification::make()
            ->title('Recordatorios enviados')
            ->body("Se enviaron {$count} emails a usuarios pendientes")
            ->success()
            ->send();
    }
}
