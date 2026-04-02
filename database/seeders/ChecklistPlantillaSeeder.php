<?php

namespace Database\Seeders;

use App\Models\ChecklistPlantilla;
use Illuminate\Database\Seeder;

class ChecklistPlantillaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();

        foreach ($empresas as $empresa) {
            ChecklistPlantilla::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => 'Verificacion mensual de PC'],
                [
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
                ]
            );

            ChecklistPlantilla::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => 'Revision trimestral de seguridad'],
                [
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
                ]
            );
        }
    }
}
