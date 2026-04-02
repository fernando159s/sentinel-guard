<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->foreign('equipo_id')->references('id')->on('equipos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo'); // asignacion, transferencia, devolucion, baja
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin')->nullable(); // null = vigente
            $table->string('condicion_entrega')->nullable(); // bueno, regular, malo
            $table->string('condicion_devolucion')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('asignado_por')->constrained('users');
            $table->timestamps();

            $table->index(['equipo_id', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_asignaciones');
    }
};
