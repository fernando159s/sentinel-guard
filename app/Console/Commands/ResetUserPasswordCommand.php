<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Restablece la contraseña de un usuario de forma segura, pensado para correr
 * en el servidor (incluida producción) cuando alguien pierde el acceso.
 *
 * La nueva contraseña se pide con entrada OCULTA: no se imprime en pantalla, no
 * se pasa como argumento y no queda en el historial del shell. El modelo User
 * castea 'password' => 'hashed', por lo que al asignar el texto plano se hashea
 * una sola vez (no usar Hash::make aquí: provocaría doble hash).
 *
 * Uso: php artisan user:reset-password administracion@torreblancamarmanillo.com
 */
class ResetUserPasswordCommand extends Command
{
    protected $signature = 'user:reset-password {email : Email del usuario}
                            {--logout : Cierra las sesiones/tokens API existentes del usuario tras el cambio}';

    protected $description = 'Restablece la contraseña de un usuario de forma segura (entrada oculta, sin exponerla)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No existe un usuario con el email {$email}.");

            return self::FAILURE;
        }

        $empresa = $user->empresa?->razon_social
            ?? ($user->empresa_id ? "empresa #{$user->empresa_id}" : 'sin empresa');
        $rol = $user->roles->pluck('name')->implode(', ') ?: 'sin rol';

        $this->newLine();
        $this->info('Usuario encontrado:');
        $this->line("  Nombre : {$user->name}");
        $this->line("  Email  : {$user->email}");
        $this->line("  Empresa: {$empresa}");
        $this->line("  Rol    : {$rol}");
        $this->newLine();

        if (! $this->confirm('¿Restablecer la contraseña de este usuario?')) {
            $this->comment('Operación cancelada. No se cambió nada.');

            return self::SUCCESS;
        }

        $nueva = (string) $this->secret('Nueva contraseña (no se mostrará)');

        if (strlen($nueva) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres. No se cambió nada.');

            return self::FAILURE;
        }

        if ($nueva !== (string) $this->secret('Confirma la nueva contraseña')) {
            $this->error('Las contraseñas no coinciden. No se cambió nada.');

            return self::FAILURE;
        }

        // El cast 'hashed' del modelo hashea al guardar (una sola vez).
        $user->password = $nueva;
        $user->save();

        if ($this->option('logout')) {
            $user->tokens()->delete();
            $this->line('  Tokens de API del usuario revocados.');
        }

        $this->newLine();
        $this->info("✅ Contraseña actualizada para {$user->email}.");
        $this->comment('Por seguridad no se mostró la contraseña. Compártela por un canal seguro, nunca por chat.');

        return self::SUCCESS;
    }
}
