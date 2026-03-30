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
        Schema::create('registros', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->unsignedInteger('empresa_id');
            $table->enum('tipo_formato', [
                'F01', 'F02', 'F03', 'F04', 'F05', 'F06', 'F07',
                'F08', 'F09', 'F10', 'F11', 'F12', 'F13',
            ]);
            $table->string('numero_registro', 20);
            $table->json('datos');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedBigInteger('creado_por');
            $table->unsignedBigInteger('modificado_por')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');

            $table->foreign('creado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            $table->foreign('modificado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->unique(['empresa_id', 'numero_registro']);
            $table->index(['empresa_id', 'tipo_formato']);
            $table->index(['empresa_id', 'created_at']);
            $table->index('creado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
