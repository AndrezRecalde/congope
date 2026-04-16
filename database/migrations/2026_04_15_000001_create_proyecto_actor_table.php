<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla pivote que relaciona proyectos con uno o varios actores cooperantes.
     */
    public function up(): void
    {
        Schema::create('proyecto_actor', function (Blueprint $table) {
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->constrained('actores_cooperacion')->restrictOnDelete();
            $table->primary(['proyecto_id', 'actor_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_actor');
    }
};
