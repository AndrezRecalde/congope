<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyecto_beneficiario', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignUuid('provincia_id')->constrained('provincias')->cascadeOnDelete();
            $table->foreignId('categoria_beneficiario_id')->constrained('categorias_beneficiario')->restrictOnDelete();
            $table->unsignedInteger('cantidad_directos')->nullable();
            $table->unsignedInteger('cantidad_indirectos')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Una categoría puede registrarse una vez por provincia dentro del mismo proyecto
            $table->unique(['proyecto_id', 'provincia_id', 'categoria_beneficiario_id'], 'ub_proy_prov_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_beneficiario');
    }
};
