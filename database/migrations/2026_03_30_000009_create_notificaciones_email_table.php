<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificaciones_email', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->string('destinatario', 150);
            $table->string('nombre_destino', 150)->nullable();
            $table->string('asunto', 300);
            $table->longText('cuerpo_html');
            $table->enum('estado', ['pendiente', 'enviado', 'error'])->default('pendiente');
            $table->tinyInteger('intentos')->default(0);
            $table->text('error_mensaje')->nullable();
            $table->dateTime('fecha_programada')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('fecha_envio')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['estado', 'fecha_programada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_email');
    }
};
