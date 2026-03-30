<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('empresa_id')->nullable()->after('id');
            $table->enum('rol', [
                'super_admin',
                'admin_empresa',
                'usuario',
                'agente_helpdesk',
                'solo_lectura',
            ])->default('usuario')->after('remember_token');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('rol');
            $table->tinyInteger('intentos_fallidos')->default(0)->after('estado');
            $table->dateTime('bloqueado_hasta')->nullable()->after('intentos_fallidos');
            $table->boolean('notif_tickets')->default(true)->after('bloqueado_hasta');
            $table->boolean('notif_incidencias')->default(true)->after('notif_tickets');
            $table->dateTime('ultimo_acceso')->nullable()->after('notif_incidencias');

            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('set null');

            $table->index('empresa_id');
            $table->index('rol');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropIndex(['empresa_id']);
            $table->dropIndex(['rol']);
            $table->dropIndex(['estado']);
            $table->dropColumn([
                'empresa_id',
                'rol',
                'estado',
                'intentos_fallidos',
                'bloqueado_hasta',
                'notif_tickets',
                'notif_incidencias',
                'ultimo_acceso',
            ]);
        });
    }
};
