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
        Schema::create('hitos_proyecto', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->string('titulo', 300);
            $table->text('descripcion')->nullable();
            $table->date('fecha_limite');
            $table->boolean('completado')->default(false);
            $table->date('completado_en')->nullable();
            $table->timestamps();

            $table->index('proyecto_id');
            $table->index('fecha_limite');
            $table->index('completado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitos_proyecto');
    }
};
