<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacitaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->string('tema', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->unsignedSmallInteger('duracion_minutos');
            $table->string('modalidad', 20); // presencial, virtual
            $table->string('expositor', 255);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->foreign('registro_id')->references('id')->on('registros')->nullOnDelete();
            $table->index('empresa_id');
            $table->index('fecha');
        });

        Schema::create('capacitacion_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacitacion_id')->constrained('capacitaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_confirmacion')->nullable();
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->unique(['capacitacion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacitacion_asistencias');
        Schema::dropIfExists('capacitaciones');
    }
};
