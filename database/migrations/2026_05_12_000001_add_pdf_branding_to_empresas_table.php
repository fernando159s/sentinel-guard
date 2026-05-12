<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('logo_documentos_path')->nullable()->after('logo_path');
            $table->string('pdf_color_primario', 7)->nullable()->after('color_sidebar');
            $table->string('pdf_color_secundario', 7)->nullable()->after('pdf_color_primario');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['logo_documentos_path', 'pdf_color_primario', 'pdf_color_secundario']);
        });
    }
};
