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
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('color_primario', 7)->nullable()->after('logo_path');
            $table->string('color_secundario', 7)->nullable()->after('color_primario');
            $table->string('color_sidebar', 7)->nullable()->after('color_secundario');
            $table->string('nombre_portal', 100)->nullable()->after('color_sidebar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['color_primario', 'color_secundario', 'color_sidebar', 'nombre_portal']);
        });
    }
};
