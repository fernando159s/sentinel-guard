<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('nombre', 150);
            $table->string('asunto', 300);
            $table->longText('contenido');
            $table->json('variables_disponibles');
            $table->longText('plantilla_default_asunto');
            $table->longText('plantilla_default_contenido');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
