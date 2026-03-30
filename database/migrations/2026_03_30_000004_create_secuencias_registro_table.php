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
        Schema::create('secuencias_registro', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedInteger('empresa_id');
            $table->string('tipo_formato', 5);
            $table->smallInteger('anio')->unsigned();
            $table->integer('ultimo_seq')->default(0);

            $table->primary(['empresa_id', 'tipo_formato', 'anio']);

            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secuencias_registro');
    }
};
