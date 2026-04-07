<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_ejecuciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('programacion_id');
            $table->foreign('programacion_id')->references('id')->on('backup_programaciones')->cascadeOnDelete();
            $table->dateTime('fecha_ejecucion');
            $table->unsignedBigInteger('ejecutado_por');
            $table->foreign('ejecutado_por')->references('id')->on('users');
            $table->string('estado'); // ejecutado, pendiente, atrasado
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->foreign('registro_id')->references('id')->on('registros')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_ejecuciones');
    }
};
