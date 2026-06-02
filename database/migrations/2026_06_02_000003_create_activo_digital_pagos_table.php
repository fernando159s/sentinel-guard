<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_digital_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo_digital_id');
            $table->foreign('activo_digital_id')->references('id')->on('activos_digitales')->cascadeOnDelete();
            $table->date('fecha_pago');
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('PEN');
            $table->string('metodo')->nullable(); // tarjeta, transferencia, paypal...
            $table->date('periodo_desde')->nullable();
            $table->date('periodo_hasta')->nullable();
            $table->string('comprobante')->nullable(); // path del archivo (FileUpload)
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['activo_digital_id', 'fecha_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_digital_pagos');
    }
};
