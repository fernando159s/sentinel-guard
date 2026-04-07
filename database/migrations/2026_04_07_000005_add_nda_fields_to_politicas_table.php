<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->boolean('es_nda')->default(false)->after('activa');
            $table->unsignedSmallInteger('vigencia_meses')->nullable()->after('es_nda');
        });
    }

    public function down(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->dropColumn(['es_nda', 'vigencia_meses']);
        });
    }
};
