<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set all non-NDA policies to informational (no user signature required)
        DB::table('politicas')
            ->where('es_nda', false)
            ->update(['obligatoria' => false]);

        // Keep Línea Base de Seguridad as mandatory
        DB::table('politicas')
            ->where('slug', 'politica-de-linea-base-de-seguridad')
            ->update(['obligatoria' => true]);
    }

    public function down(): void
    {
        // Restore all originally-mandatory non-NDA policies
        $slugs = [
            'politica-de-linea-base-de-seguridad',
            'politica-de-bloqueo-de-correos-masivos',
            'procedimiento-de-comunicacion',
            'politica-de-control-de-acceso-a-dispositivos',
            'politica-de-control-de-acceso-a-paginas-web-no-autorizadas',
            'politica-de-control-de-cambios',
            'politica-de-control-de-envio-de-datos-sensibles',
            'politica-de-gestion-de-accesos-a-la-red',
            'procedimiento-de-gestion-de-riesgos-y-oportunidades',
            'politica-de-gestion-de-vulnerabilidades',
            'procedimiento-de-mejora-continua',
            'procedimiento-de-seleccion-y-contratacion-de-personal',
            'politica-de-seguridad-para-la-proteccion-de-datos-personales',
            'procedimiento-de-prueba-de-planes-y-respuesta-a-incidentes',
            'politica-de-antivirus-y-proteccion-contra-malware',
            'politica-de-autenticacion-multifactor-mfa',
            'manual-del-sistema-de-gestion-de-seguridad-de-la-informacion',
            'manual-de-organizacion-y-funciones-mof',
        ];

        DB::table('politicas')
            ->whereIn('slug', $slugs)
            ->update(['obligatoria' => true]);
    }
};
