<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Emite un token de API (Sanctum) para conectar un cliente MCP (Claude Code,
 * Claude Desktop, Cline, etc.) como un usuario concreto. El cliente usará el
 * token en el header Authorization; cada herramienta correrá como ese usuario
 * y respetará su empresa (tenant) y permisos.
 *
 * Uso: php artisan mcp:token carlos@palacios.pe
 */
class McpTokenCommand extends Command
{
    protected $signature = 'mcp:token {email : Email del usuario} {--name=cliente-mcp : Nombre/etiqueta del token}';

    protected $description = 'Emite un token de API (Sanctum) para conectar un cliente MCP como el usuario indicado';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No existe un usuario con el email {$this->argument('email')}.");

            return self::FAILURE;
        }

        if (! $user->hasRole(['super_admin', 'admin_empresa'])) {
            $this->warn('Aviso: este usuario no tiene rol de gestión (super_admin/admin_empresa); los tools de ingesta lo rechazarán.');
        }

        $token = $user->createToken($this->option('name'))->plainTextToken;
        $url = rtrim((string) config('app.url'), '/').'/mcp/ingesta';
        $alcance = $user->empresa_id ? "empresa #{$user->empresa_id}" : 'super admin (todas las empresas)';

        $this->newLine();
        $this->info("Token para {$user->name} <{$user->email}> — {$alcance}:");
        $this->line("  {$token}");
        $this->newLine();
        $this->comment('Conéctalo en Claude Code (ajusta el host si no es local):');
        $this->line("  claude mcp add --transport http securiform {$url} --header \"Authorization: Bearer {$token}\"");
        $this->newLine();
        $this->comment('El token solo se muestra ahora. Guárdalo en un lugar seguro; revócalo con: $user->tokens()->delete().');

        return self::SUCCESS;
    }
}
