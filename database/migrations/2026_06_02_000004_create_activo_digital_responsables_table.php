<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_digital_responsables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo_digital_id');
            $table->foreign('activo_digital_id')->references('id')->on('activos_digitales')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['activo_digital_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_digital_responsables');
    }
};
