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
        Schema::create('buenas_practicas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provincia_id')->constrained('provincias')->restrictOnDelete();
            $table->foreignUuid('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->string('titulo', 500);
            $table->text('descripcion_problema');
            $table->text('metodologia');
            $table->text('resultados');
            $table->enum('replicabilidad', ['Alta', 'Media', 'Baja']);
            $table->decimal('calificacion_promedio', 3, 2)->default(0.00);
            $table->boolean('es_destacada')->default(false);
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('provincia_id');
            $table->index('replicabilidad');
            $table->index('es_destacada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buenas_practicas');
    }
};
