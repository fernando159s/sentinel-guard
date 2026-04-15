<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->longText('firma_admin_imagen')->nullable()->after('vigencia_meses');
            $table->string('firma_admin_nombre')->nullable()->after('firma_admin_imagen');
            $table->string('firma_admin_cargo')->default('Gerente General')->after('firma_admin_nombre');
            $table->unsignedBigInteger('firmado_por')->nullable()->after('firma_admin_cargo');
            $table->timestamp('fecha_firma_admin')->nullable()->after('firmado_por');

            $table->foreign('firmado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->dropForeign(['firmado_por']);
            $table->dropColumn([
                'firma_admin_imagen',
                'firma_admin_nombre',
                'firma_admin_cargo',
                'firmado_por',
                'fecha_firma_admin',
            ]);
        });
    }
};
