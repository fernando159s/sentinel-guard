<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_digital_credenciales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo_digital_id');
            $table->foreign('activo_digital_id')->references('id')->on('activos_digitales')->cascadeOnDelete();
            $table->string('etiqueta'); // "admin principal", "API key"
            // Campos sensibles: se almacenan cifrados via casts 'encrypted' en el modelo (TEXT para el ciphertext)
            $table->text('usuario')->nullable();
            $table->text('password')->nullable();
            $table->text('dato_2fa')->nullable(); // seed TOTP
            $table->text('recovery')->nullable(); // codigos de recuperacion
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_digital_credenciales');
    }
};
