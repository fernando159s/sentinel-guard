<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seeder para primera instalación en producción.
     * Crea roles/permisos, templates globales y un super admin.
     *
     * Uso: php artisan db:seed --class=ProductionSeeder
     */
    public function run(): void
    {
        // 1. Roles y permisos (requerido antes de cualquier usuario)
        $this->call(RolePermissionSeeder::class);

        // 2. Templates de email (globales, no dependen de empresa)
        $this->call(EmailTemplateSeeder::class);

        // 3. Super admin inicial
        $this->createSuperAdmin();
    }

    private function createSuperAdmin(): void
    {
        // Leer directo del .env (env() no funciona con config cacheado)
        $envValues = $this->parseEnvFile();
        $email = $envValues['ADMIN_EMAIL'] ?? 'admin@securiform.local';
        $password = $envValues['ADMIN_PASSWORD'] ?? null;

        if (! $password) {
            $this->command->error('Debe definir ADMIN_PASSWORD en .env para crear el super admin.');
            $this->command->info('Ejemplo: ADMIN_EMAIL=admin@empresa.com ADMIN_PASSWORD=MiClave123!');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrador',
                'password' => $password,
                'empresa_id' => null,
                'rol' => 'super_admin',
                'estado' => 'activo',
            ]
        );

        $user->syncRoles('super_admin');

        $this->command->info("Super admin creado: {$email}");
    }

    private function parseEnvFile(): array
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return [];
        }

        $values = [];

        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        return $values;
    }
}
