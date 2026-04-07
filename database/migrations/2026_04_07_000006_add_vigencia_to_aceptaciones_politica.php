<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aceptaciones_politica', function (Blueprint $table) {
            $table->dateTime('fecha_expiracion')->nullable()->after('user_agent');
            $table->index(['user_id', 'politica_id', 'fecha_expiracion'], 'aceptaciones_vigencia_idx');
        });
    }

    public function down(): void
    {
        Schema::table('aceptaciones_politica', function (Blueprint $table) {
            $table->dropIndex('aceptaciones_vigencia_idx');
            $table->dropColumn('fecha_expiracion');
        });
    }
};
