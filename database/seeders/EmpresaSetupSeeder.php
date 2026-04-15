<?php

namespace Database\Seeders;

use App\Models\ChecklistPlantilla;
use App\Models\Empresa;
use App\Models\Politica;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmpresaSetupSeeder extends Seeder
{
    /**
     * Instalador completo de empresa para producción.
     * Crea empresa + admin + políticas + checklists en un solo paso.
     *
     * Se usa desde el comando: php artisan empresa:setup
     * O directamente:         php artisan db:seed --class=EmpresaSetupSeeder
     *
     * Parámetros esperados vía $this->command (modo interactivo)
     * o vía variables de entorno:
     *   EMPRESA_RUC, EMPRESA_RAZON_SOCIAL, EMPRESA_EMAIL,
     *   EMPRESA_ADMIN_NAME, EMPRESA_ADMIN_EMAIL, EMPRESA_ADMIN_PASSWORD
     */
    public function run(): void
    {
        // 1. Asegurar que roles y permisos existen
        $this->ensureRolesExist();

        // 2. Crear empresa
        $empresa = $this->createEmpresa();

        if (! $empresa) {
            return;
        }

        // 3. Crear admin de la empresa
        $this->createAdminEmpresa($empresa);

        // 4. Crear políticas para la empresa
        $this->createPoliticas($empresa);

        // 5. Crear plantillas de checklist
        $this->createChecklists($empresa);

        $this->command->newLine();
        $this->command->info("=== Empresa '{$empresa->razon_social}' configurada exitosamente ===");
        $this->command->newLine();
    }

    private function ensureRolesExist(): void
    {
        $rolesCount = \Spatie\Permission\Models\Role::count();

        if ($rolesCount === 0) {
            $this->command->warn('Roles no encontrados. Ejecutando RolePermissionSeeder...');
            $this->call(RolePermissionSeeder::class);
        }
    }

    private function createEmpresa(): ?Empresa
    {
        $ruc = env('EMPRESA_RUC') ?: $this->command->ask('RUC de la empresa');
        $razonSocial = env('EMPRESA_RAZON_SOCIAL') ?: $this->command->ask('Razón social');
        $email = env('EMPRESA_EMAIL') ?: $this->command->ask('Email de la empresa');
        $direccion = env('EMPRESA_DIRECCION') ?: $this->command->ask('Dirección (opcional)', '');
        $telefono = env('EMPRESA_TELEFONO') ?: $this->command->ask('Teléfono (opcional)', '');

        if (! $ruc || ! $razonSocial || ! $email) {
            $this->command->error('RUC, razón social y email son obligatorios.');

            return null;
        }

        $existing = Empresa::where('ruc', $ruc)->first();

        if ($existing) {
            $this->command->warn("La empresa con RUC {$ruc} ya existe: {$existing->razon_social}");

            if (! $this->command->confirm('¿Desea continuar y actualizar sus datos?', false)) {
                return null;
            }
        }

        $empresa = Empresa::updateOrCreate(
            ['ruc' => $ruc],
            [
                'razon_social' => $razonSocial,
                'email' => $email,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'estado' => 'activo',
            ]
        );

        $this->command->info("Empresa creada: {$empresa->razon_social} (ID: {$empresa->id})");

        return $empresa;
    }

    private function createAdminEmpresa(Empresa $empresa): void
    {
        $name = env('EMPRESA_ADMIN_NAME') ?: $this->command->ask('Nombre del administrador');
        $email = env('EMPRESA_ADMIN_EMAIL') ?: $this->command->ask('Email del administrador');
        $password = env('EMPRESA_ADMIN_PASSWORD') ?: $this->command->secret('Contraseña del administrador');

        if (! $name || ! $email || ! $password) {
            $this->command->error('Nombre, email y contraseña del admin son obligatorios.');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'empresa_id' => $empresa->id,
                'rol' => 'admin_empresa',
                'estado' => 'activo',
            ]
        );

        $user->syncRoles('admin_empresa');

        $this->command->info("Admin creado: {$email} (rol: admin_empresa)");
    }

    private function createPoliticas(Empresa $empresa): void
    {
        $politicaSeeder = new PoliticaSeeder;
        $politicas = $politicaSeeder->getPoliticas();

        $count = 0;

        foreach ($politicas as $data) {
            Politica::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'slug' => Str::slug($data['titulo']),
                ],
                array_merge($data, ['empresa_id' => $empresa->id])
            );
            $count++;
        }

        $this->command->info("Políticas creadas: {$count}");
    }

    private function createChecklists(Empresa $empresa): void
    {
        $checklists = $this->getChecklistPlantillas();
        $count = 0;

        foreach ($checklists as $checklist) {
            ChecklistPlantilla::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $checklist['nombre']],
                array_merge($checklist, ['empresa_id' => $empresa->id])
            );
            $count++;
        }

        $this->command->info("Plantillas de checklist creadas: {$count}");
    }

    private function getChecklistPlantillas(): array
    {
        return [
            [
                'nombre' => 'Verificacion mensual de PC',
                'descripcion' => 'Verificacion mensual de seguridad para equipos de computo. Asegura que el equipo cumple con las politicas PSC000003 y PSC000004.',
                'periodicidad' => 'mensual',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Antivirus actualizado', 'descripcion' => 'El antivirus tiene definiciones de virus actualizadas (menos de 7 dias)', 'obligatorio' => true],
                    ['nombre' => 'Windows Update al dia', 'descripcion' => 'No hay actualizaciones criticas pendientes de instalar', 'obligatorio' => true],
                    ['nombre' => 'BitLocker activo', 'descripcion' => 'El cifrado de disco BitLocker esta activado (PSC000-46)', 'obligatorio' => true],
                    ['nombre' => 'Sin software no autorizado', 'descripcion' => 'No se encontro software instalado que no este en la lista aprobada', 'obligatorio' => true],
                    ['nombre' => 'Pantalla de bloqueo configurada', 'descripcion' => 'El equipo se bloquea automaticamente despues de 5 minutos de inactividad', 'obligatorio' => true],
                    ['nombre' => 'Backup reciente', 'descripcion' => 'Existe una copia de seguridad de los datos del usuario de menos de 30 dias', 'obligatorio' => false],
                    ['nombre' => 'Sin datos sensibles en escritorio', 'descripcion' => 'No hay archivos con datos personales o confidenciales en el escritorio', 'obligatorio' => false],
                ],
            ],
            [
                'nombre' => 'Revision trimestral de seguridad',
                'descripcion' => 'Revision trimestral profunda de seguridad. Incluye verificacion de contrasenas, permisos y configuracion de red.',
                'periodicidad' => 'trimestral',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Contrasena cambiada en ultimos 90 dias', 'descripcion' => 'El usuario ha cambiado su contrasena en el periodo', 'obligatorio' => true],
                    ['nombre' => 'Permisos de carpetas correctos', 'descripcion' => 'Las carpetas compartidas tienen los permisos adecuados, sin accesos excesivos', 'obligatorio' => true],
                    ['nombre' => 'USB deshabilitado (si aplica)', 'descripcion' => 'Los puertos USB estan deshabilitados via GPO si el equipo maneja datos sensibles (PSC000-28)', 'obligatorio' => false],
                    ['nombre' => 'Firewall activo', 'descripcion' => 'El firewall de Windows esta activo y configurado correctamente', 'obligatorio' => true],
                    ['nombre' => 'Sin conexiones VPN no autorizadas', 'descripcion' => 'No hay clientes VPN no aprobados instalados', 'obligatorio' => true],
                ],
            ],
        ];
    }
}
