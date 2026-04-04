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
        Schema::create('reconocimientos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('emblematico_id')->constrained('proyectos_emblematicos')->cascadeOnDelete();
            $table->string('titulo', 300);
            $table->string('organismo_otorgante', 300);
            $table->enum('ambito', ['Nacional', 'Internacional']);
            $table->unsignedSmallInteger('anio');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index('emblematico_id');
            $table->index('anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconocimientos');
    }
};
