<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->string('tipo'); // pc_escritorio, laptop, impresora, servidor, usb, otro
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable()->unique();
            $table->string('codigo_interno')->nullable(); // EQ-001
            $table->string('sistema_operativo')->nullable();
            $table->string('procesador')->nullable();
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->unsignedSmallInteger('disco_gb')->nullable();
            $table->string('estado')->default('activo'); // activo, mantenimiento, obsoleto, dado_de_baja
            $table->string('ubicacion')->nullable();
            $table->string('nivel_sensibilidad')->default('interno'); // publico, interno, confidencial, sensible
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_garantia')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
