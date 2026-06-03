<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions per resource
        $resources = [
            'empresas',
            'usuarios',
            'registros',
            'tickets',
            'helpdesk',
            'auditoria',
            'exportar',
        ];

        $actions = ['ver', 'crear', 'editar', 'eliminar'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$resource}"]);
            }
        }

        // Extra permissions
        Permission::firstOrCreate(['name' => 'gestionar_empresa_propia']);
        Permission::firstOrCreate(['name' => 'ver_notas_internas']);
        Permission::firstOrCreate(['name' => 'asignar_tickets']);
        // Activos digitales (EP-19): acceso a credenciales sensibles
        Permission::firstOrCreate(['name' => 'ver_credenciales']);

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $adminEmpresa = Role::firstOrCreate(['name' => 'admin_empresa']);
        $adminEmpresa->syncPermissions([
            'ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios',
            'ver_registros', 'crear_registros', 'editar_registros', 'eliminar_registros',
            'ver_tickets', 'crear_tickets',
            'ver_exportar', 'crear_exportar',
            'gestionar_empresa_propia',
            'ver_credenciales',
        ]);

        $usuario = Role::firstOrCreate(['name' => 'usuario']);
        $usuario->syncPermissions([
            'ver_registros', 'crear_registros', 'editar_registros',
            'ver_tickets', 'crear_tickets',
            'ver_exportar', 'crear_exportar',
        ]);

        $agente = Role::firstOrCreate(['name' => 'agente_helpdesk']);
        $agente->syncPermissions([
            'ver_tickets', 'editar_tickets',
            'ver_helpdesk', 'crear_helpdesk', 'editar_helpdesk',
            'ver_notas_internas', 'asignar_tickets',
            'ver_exportar', 'crear_exportar',
            'crear_registros',
        ]);

        $soloLectura = Role::firstOrCreate(['name' => 'solo_lectura']);
        $soloLectura->syncPermissions([
            'ver_registros',
            'ver_tickets',
            'ver_exportar',
        ]);
    }
}
