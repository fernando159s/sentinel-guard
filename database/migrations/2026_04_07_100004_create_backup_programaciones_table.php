<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_programaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->unsignedBigInteger('equipo_id')->nullable();
            $table->foreign('equipo_id')->references('id')->on('equipos')->nullOnDelete();
            $table->string('nombre');
            $table->string('periodicidad'); // diaria, semanal, quincenal, mensual, trimestral, puntual
            $table->date('proximo_backup');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('creado_por');
            $table->foreign('creado_por')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'activo']);
            $table->index(['proximo_backup', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_programaciones');
    }
};
