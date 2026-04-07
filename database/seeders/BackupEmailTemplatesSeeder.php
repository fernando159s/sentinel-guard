<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class BackupEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'backup-proximo',
                'nombre' => 'Recordatorio de backup proximo',
                'asunto' => 'Recordatorio: Backup programado para mañana — {{nombre_backup}}',
                'contenido' => "Estimado administrador,\n\nLe recordamos que el backup \"{{nombre_backup}}\" esta programado para mañana {{fecha_programada}}.\n\nEquipo asociado: {{equipo}}\nEmpresa: {{empresa}}\n\nPor favor asegurese de que el backup se ejecute correctamente y marque la ejecucion en el sistema.\n\nSaludos,\nSecuriForm",
                'variables_disponibles' => ['nombre_backup', 'fecha_programada', 'equipo', 'empresa'],
            ],
            [
                'slug' => 'backup-atrasado',
                'nombre' => 'Alerta de backup atrasado',
                'asunto' => 'ALERTA: Backup atrasado — {{nombre_backup}}',
                'contenido' => "Estimado administrador,\n\nEl backup \"{{nombre_backup}}\" esta ATRASADO.\n\nFecha programada: {{fecha_programada}}\nDias de atraso: {{dias_atraso}}\nEquipo asociado: {{equipo}}\nEmpresa: {{empresa}}\n\nEste backup no ha sido marcado como ejecutado. Por favor tome accion inmediata para cumplir con la politica de copias de seguridad (PSC000003/PSC000-15).\n\nSaludos,\nSecuriForm",
                'variables_disponibles' => ['nombre_backup', 'fecha_programada', 'dias_atraso', 'equipo', 'empresa'],
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'nombre' => $template['nombre'],
                    'asunto' => $template['asunto'],
                    'contenido' => $template['contenido'],
                    'variables_disponibles' => $template['variables_disponibles'],
                    'plantilla_default_asunto' => $template['asunto'],
                    'plantilla_default_contenido' => $template['contenido'],
                    'activo' => true,
                ]
            );
        }
    }
}
