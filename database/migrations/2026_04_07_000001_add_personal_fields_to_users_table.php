<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 20)->nullable()->after('email');
            $table->string('direccion', 500)->nullable()->after('dni');
            $table->string('telefono', 30)->nullable()->after('direccion');
            $table->string('puesto', 150)->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni', 'direccion', 'telefono', 'puesto']);
        });
    }
};
