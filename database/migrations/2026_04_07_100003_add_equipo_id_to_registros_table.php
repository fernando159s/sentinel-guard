<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->unsignedBigInteger('equipo_id')->nullable()->after('modificado_por');
            $table->foreign('equipo_id')->references('id')->on('equipos')->nullOnDelete();
            $table->index('equipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
            $table->dropIndex(['equipo_id']);
            $table->dropColumn('equipo_id');
        });
    }
};
