<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyectos_emblematicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->restrictOnDelete();
            $table->foreignUuid('provincia_id')->constrained('provincias')->restrictOnDelete();
            $table->string('titulo', 500);
            $table->text('descripcion_impacto');
            $table->string('periodo', 50)->nullable();
            $table->boolean('es_publico')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('es_publico');
            $table->index('provincia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos_emblematicos');
    }
};
