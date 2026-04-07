<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->string('archivo_path', 500)->nullable()->after('contenido');
            $table->string('archivo_nombre', 255)->nullable()->after('archivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->dropColumn(['archivo_path', 'archivo_nombre']);
        });
    }
};
