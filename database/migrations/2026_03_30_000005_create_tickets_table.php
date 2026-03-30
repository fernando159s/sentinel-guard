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
        Schema::create('tickets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('numero_ticket', 15)->unique();
            $table->unsignedBigInteger('creado_por');
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->string('asunto', 300);
            $table->text('descripcion');
            $table->enum('categoria', [
                'consulta',
                'problema_tecnico',
                'error_registro',
                'solicitud_acceso',
                'otro',
            ])->default('consulta');
            $table->enum('prioridad', [
                'baja',
                'media',
                'alta',
                'urgente',
            ])->default('media');
            $table->enum('estado', [
                'nuevo',
                'en_revision',
                'esperando_usuario',
                'resuelto',
                'cerrado',
            ])->default('nuevo');
            $table->dateTime('fecha_ultima_actividad');
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');

            $table->foreign('creado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            $table->foreign('asignado_a')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('empresa_id');
            $table->index('estado');
            $table->index('asignado_a');
            $table->index('prioridad');
            $table->index('fecha_ultima_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
