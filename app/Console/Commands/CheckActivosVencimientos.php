<?php

namespace App\Console\Commands;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Models\ActivoDigital;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckActivosVencimientos extends Command
{
    protected $signature = 'activos:check-vencimientos';

    protected $description = 'Marca activos digitales vencidos y envia alertas de renovacion proxima (30/7/1 dias)';

    /** Dias de anticipacion para avisar de un vencimiento proximo. */
    private const DIAS_AVISO = [30, 7, 1];

    public function handle(): void
    {
        $this->checkProximos();
        $this->checkVencidos();
    }

    private function recurrentes()
    {
        return ActivoDigital::query()
            ->withoutGlobalScopes()
            ->whereIn('modalidad_pago', [ModalidadPago::Mensual->value, ModalidadPago::Anual->value])
            ->whereNotNull('fecha_vencimiento');
    }

    private function checkProximos(): void
    {
        foreach (self::DIAS_AVISO as $dias) {
            $fecha = now()->addDays($dias)->toDateString();

            $proximos = $this->recurrentes()
                ->where('estado', '!=', EstadoActivoDigital::Cancelado->value)
                ->whereDate('fecha_vencimiento', $fecha)
                ->get();

            foreach ($proximos as $activo) {
                $this->notificar($activo, 'activo-por-vencer', [
                    'fecha_vencimiento' => $activo->fecha_vencimiento->format('d/m/Y'),
                    'dias' => (string) $dias,
                ]);

                $this->info("Por vencer ({$dias}d): {$activo->codigo_interno} {$activo->nombre}");
            }
        }
    }

    private function checkVencidos(): void
    {
        $vencidos = $this->recurrentes()
            ->whereIn('estado', [EstadoActivoDigital::Activo->value, EstadoActivoDigital::Suspendido->value])
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->get();

        foreach ($vencidos as $activo) {
            $activo->update(['estado' => EstadoActivoDigital::Vencido->value]);

            $this->notificar($activo, 'activo-vencido', [
                'fecha_vencimiento' => $activo->fecha_vencimiento->format('d/m/Y'),
            ]);

            $this->warn("Vencido: {$activo->codigo_interno} {$activo->nombre}");
        }
    }

    /**
     * Envia el correo a admins de la empresa + responsables de la cuenta.
     */
    private function notificar(ActivoDigital $activo, string $slug, array $extraVars): void
    {
        $template = EmailTemplate::findBySlug($slug);

        if (! $template) {
            return;
        }

        $admins = User::where('empresa_id', $activo->empresa_id)
            ->role('admin_empresa')
            ->get();

        $destinatarios = $admins->merge($activo->responsables)->unique('id');

        foreach ($destinatarios as $user) {
            $vars = array_merge([
                'nombre' => $user->name,
                'nombre_activo' => $activo->nombre,
                'codigo' => $activo->codigo_interno,
                'proveedor' => $activo->proveedor ?? '—',
                'empresa' => $activo->empresa?->razon_social ?? '',
            ], $extraVars);

            $subject = $template->renderAsunto($vars);
            $body = $template->renderContenido($vars);

            Mail::raw(strip_tags($body), function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });
        }
    }
}
