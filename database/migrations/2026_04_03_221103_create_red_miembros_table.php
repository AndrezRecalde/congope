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
        Schema::create('red_miembros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('red_id')->constrained('redes')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->constrained('actores_cooperacion')->cascadeOnDelete();
            $table->string('rol_miembro', 150)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->timestamps();

            $table->unique(['red_id', 'actor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('red_miembros');
    }
};
