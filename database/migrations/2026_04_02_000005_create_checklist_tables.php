<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_plantillas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->json('items'); // [{nombre, descripcion, obligatorio}]
            $table->string('periodicidad')->default('mensual');
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checklist_ejecuciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_plantilla_id')->constrained('checklist_plantillas')->cascadeOnDelete();
            $table->unsignedBigInteger('equipo_id');
            $table->foreign('equipo_id')->references('id')->on('equipos')->cascadeOnDelete();
            $table->foreignId('ejecutado_por')->constrained('users');
            $table->datetime('fecha_ejecucion');
            $table->json('resultados'); // [{item, cumple, observacion}]
            $table->string('estado')->default('pendiente');
            $table->text('observaciones_generales')->nullable();
            $table->timestamps();

            $table->index(['equipo_id', 'checklist_plantilla_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_ejecuciones');
        Schema::dropIfExists('checklist_plantillas');
    }
};
