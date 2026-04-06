<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Firma en cada aceptacion de politica
        Schema::table('aceptaciones_politica', function (Blueprint $table) {
            $table->longText('firma_imagen')->nullable()->after('user_agent');
            $table->string('firma_nombre')->nullable()->after('firma_imagen');
            $table->string('firma_cargo')->nullable()->after('firma_nombre');
        });

        // Firma guardada del usuario (para reusar)
        Schema::table('users', function (Blueprint $table) {
            $table->longText('firma_guardada')->nullable()->after('notif_incidencias');
        });
    }

    public function down(): void
    {
        Schema::table('aceptaciones_politica', function (Blueprint $table) {
            $table->dropColumn(['firma_imagen', 'firma_nombre', 'firma_cargo']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('firma_guardada');
        });
    }
};
