<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('slug');
            $table->longText('contenido');
            $table->string('version')->default('1.0');
            $table->boolean('obligatoria')->default(true);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'slug']);
        });

        Schema::create('aceptaciones_politica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('politica_id')->constrained('politicas')->cascadeOnDelete();
            $table->string('version_aceptada');
            $table->datetime('fecha_aceptacion');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'politica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aceptaciones_politica');
        Schema::dropIfExists('politicas');
    }
};
