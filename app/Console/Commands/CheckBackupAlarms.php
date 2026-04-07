<?php

namespace App\Console\Commands;

use App\Models\BackupEjecucion;
use App\Models\BackupProgramacion;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckBackupAlarms extends Command
{
    protected $signature = 'backups:check-alarms';

    protected $description = 'Check for upcoming and overdue backup schedules and send email alerts';

    public function handle(): void
    {
        $this->checkProximos();
        $this->checkAtrasados();
    }

    private function checkProximos(): void
    {
        $proximos = BackupProgramacion::where('activo', true)
            ->whereDate('proximo_backup', now()->addDay()->toDateString())
            ->get();

        foreach ($proximos as $prog) {
            $admins = User::where('empresa_id', $prog->empresa_id)
                ->role('admin_empresa')
                ->get();

            $template = EmailTemplate::findBySlug('backup-proximo');

            if ($template && $admins->isNotEmpty()) {
                $vars = [
                    'nombre_backup' => $prog->nombre,
                    'fecha_programada' => $prog->proximo_backup->format('d/m/Y'),
                    'equipo' => $prog->equipo?->codigo_interno ?? 'General',
                    'empresa' => $prog->empresa?->razon_social ?? '',
                ];

                $subject = $template->renderAsunto($vars);
                $body = $template->renderContenido($vars);

                foreach ($admins as $admin) {
                    Mail::raw($body, function ($message) use ($admin, $subject) {
                        $message->to($admin->email)->subject($subject);
                    });
                }
            }

            $this->info("Alerta proximo: {$prog->nombre} ({$prog->empresa?->razon_social})");
        }
    }

    private function checkAtrasados(): void
    {
        $atrasados = BackupProgramacion::where('activo', true)
            ->whereDate('proximo_backup', '<', now()->toDateString())
            ->get();

        foreach ($atrasados as $prog) {
            // Avoid duplicate atrasado entries for the same date
            $yaRegistrado = BackupEjecucion::where('programacion_id', $prog->id)
                ->where('estado', 'atrasado')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($yaRegistrado) {
                continue;
            }

            BackupEjecucion::create([
                'programacion_id' => $prog->id,
                'fecha_ejecucion' => now(),
                'ejecutado_por' => $prog->creado_por,
                'estado' => 'atrasado',
                'notas' => 'Backup atrasado detectado automaticamente.',
            ]);

            $admins = User::where('empresa_id', $prog->empresa_id)
                ->role('admin_empresa')
                ->get();

            $template = EmailTemplate::findBySlug('backup-atrasado');

            if ($template && $admins->isNotEmpty()) {
                $vars = [
                    'nombre_backup' => $prog->nombre,
                    'fecha_programada' => $prog->proximo_backup->format('d/m/Y'),
                    'dias_atraso' => (string) $prog->proximo_backup->diffInDays(now()),
                    'equipo' => $prog->equipo?->codigo_interno ?? 'General',
                    'empresa' => $prog->empresa?->razon_social ?? '',
                ];

                $subject = $template->renderAsunto($vars);
                $body = $template->renderContenido($vars);

                foreach ($admins as $admin) {
                    Mail::raw($body, function ($message) use ($admin, $subject) {
                        $message->to($admin->email)->subject($subject);
                    });
                }
            }

            $this->warn("Alerta atrasado: {$prog->nombre} ({$prog->empresa?->razon_social})");
        }
    }
}
