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
        Schema::create('proyecto_provincia', function (Blueprint $table) {
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignUuid('provincia_id')->constrained('provincias')->cascadeOnDelete();
            $table->enum('rol', ['Líder', 'Co-ejecutora', 'Beneficiaria'])->default('Beneficiaria');
            $table->unsignedTinyInteger('porcentaje_avance')->default(0);

            $table->primary(['proyecto_id', 'provincia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_provincia');
    }
};
