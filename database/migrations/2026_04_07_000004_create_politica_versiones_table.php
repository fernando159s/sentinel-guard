<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politica_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('politica_id')->constrained('politicas')->cascadeOnDelete();
            $table->string('version');
            $table->longText('contenido');
            $table->string('archivo_path', 500)->nullable();
            $table->string('archivo_nombre', 255)->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('politica_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politica_versiones');
    }
};
