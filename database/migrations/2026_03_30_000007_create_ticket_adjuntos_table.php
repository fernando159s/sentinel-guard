<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_adjuntos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger('mensaje_id');
            $table->string('nombre_original', 255);
            $table->string('nombre_almacenado', 255);
            $table->string('tipo_mime', 100);
            $table->unsignedInteger('tamano');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('mensaje_id')
                  ->references('id')
                  ->on('ticket_mensajes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_adjuntos');
    }
};
