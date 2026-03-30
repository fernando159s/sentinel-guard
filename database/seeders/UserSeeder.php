<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Administrador',
                'email' => 'admin@securiform.local',
                'password' => 'Admin2024!',
                'empresa_id' => null,
                'rol' => 'super_admin',
                'estado' => 'activo',
            ],
            [
                'name' => 'Carlos Palacios',
                'email' => 'carlos@palacios.pe',
                'password' => 'Test2024!',
                'empresa_id' => 1,
                'rol' => 'admin_empresa',
                'estado' => 'activo',
            ],
            [
                'name' => 'María García',
                'email' => 'maria@palacios.pe',
                'password' => 'Test2024!',
                'empresa_id' => 1,
                'rol' => 'usuario',
                'estado' => 'activo',
            ],
            [
                'name' => 'Pedro López',
                'email' => 'pedro@palacios.pe',
                'password' => 'Test2024!',
                'empresa_id' => 1,
                'rol' => 'solo_lectura',
                'estado' => 'activo',
            ],
            [
                'name' => 'Ana Soporte',
                'email' => 'ana@securiform.local',
                'password' => 'Test2024!',
                'empresa_id' => null,
                'rol' => 'agente_helpdesk',
                'estado' => 'activo',
            ],
            [
                'name' => 'Luis Torres',
                'email' => 'luis@techsoft.pe',
                'password' => 'Test2024!',
                'empresa_id' => 2,
                'rol' => 'admin_empresa',
                'estado' => 'activo',
            ],
            [
                'name' => 'Rosa Mendoza',
                'email' => 'rosa@techsoft.pe',
                'password' => 'Test2024!',
                'empresa_id' => 2,
                'rol' => 'usuario',
                'estado' => 'activo',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data,
            );

            $user->syncRoles($data['rol']);
        }
    }
}
