<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipo_asignaciones', function (Blueprint $table) {
            $table->string('empresa_tercera')->nullable()->after('notas');
            $table->string('ruc_tercero')->nullable()->after('empresa_tercera');
            $table->string('contacto_tercero')->nullable()->after('ruc_tercero');
            $table->text('motivo')->nullable()->after('contacto_tercero');

            // Make user_id nullable for ingreso_nuevo (no user assigned)
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('equipo_asignaciones', function (Blueprint $table) {
            $table->dropColumn(['empresa_tercera', 'ruc_tercero', 'contacto_tercero', 'motivo']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
