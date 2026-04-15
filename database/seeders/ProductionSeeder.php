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
        $email = env('ADMIN_EMAIL', 'admin@securiform.local');
        $password = env('ADMIN_PASSWORD');

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
}
