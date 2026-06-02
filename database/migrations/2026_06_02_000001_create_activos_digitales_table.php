<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activos_digitales', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->string('codigo_interno')->nullable(); // AD-001
            $table->string('nombre'); // "WhatsApp Ventas", "Meta Business Studio"
            $table->string('tipo'); // whatsapp, meta, suscripcion_saas, dominio, licencia_unica, correo, redes_sociales, otro
            $table->string('proveedor')->nullable(); // Meta, Google, OpenAI...
            $table->string('url')->nullable(); // panel de acceso
            $table->string('identificador')->nullable(); // nro telefono, email, business ID
            $table->string('estado')->default('activo'); // activo, suspendido, vencido, cancelado
            $table->string('nivel_sensibilidad')->default('interno'); // publico, interno, confidencial, sensible
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->foreign('responsable_id')->references('id')->on('users')->nullOnDelete();
            $table->string('modalidad_pago')->default('mensual'); // mensual, anual, pago_unico, gratuito
            $table->decimal('costo', 12, 2)->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->string('metodo_pago')->nullable(); // "Visa ***1234", "transferencia"
            $table->boolean('renovacion_automatica')->default(false);
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_vencimiento')->nullable(); // null si pago unico / gratuito
            // Vinculos opcionales con el resto del sistema (PSC / equipos) — US-1905
            $table->unsignedBigInteger('equipo_id')->nullable();
            $table->foreign('equipo_id')->references('id')->on('equipos')->nullOnDelete();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->foreign('registro_id')->references('id')->on('registros')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'tipo']);
            $table->index(['empresa_id', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activos_digitales');
    }
};
