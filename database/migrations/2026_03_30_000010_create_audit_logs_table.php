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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedInteger('empresa_id')->nullable();
            $table->string('accion', 100);
            $table->string('entidad', 50)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes only — NO foreign keys (audit log should never cascade delete)
            $table->index('usuario_id');
            $table->index('empresa_id');
            $table->index('accion');
            $table->index('created_at');
            $table->index(['entidad', 'entidad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
