<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('categoria')->default('tecnologico')->after('tipo');
            $table->text('contenido_datos')->nullable()->after('observaciones');
            $table->string('clasificacion_soporte')->nullable()->after('contenido_datos');

            $table->index(['empresa_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'categoria']);
            $table->dropColumn(['categoria', 'contenido_datos', 'clasificacion_soporte']);
        });
    }
};
