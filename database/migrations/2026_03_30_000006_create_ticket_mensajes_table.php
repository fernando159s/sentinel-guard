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
        Schema::create('ticket_mensajes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger('ticket_id');
            $table->unsignedBigInteger('autor_id');
            $table->enum('tipo', ['publico', 'interno'])->default('publico');
            $table->text('contenido');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->foreign('autor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_mensajes');
    }
};
