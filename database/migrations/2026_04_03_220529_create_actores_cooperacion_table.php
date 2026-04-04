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
        Schema::create('actores_cooperacion', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre', 255);
            $table->enum('tipo', ['ONG', 'Multilateral', 'Embajada', 'Bilateral', 'Privado', 'Academia']);
            $table->string('pais_origen', 100);
            $table->enum('estado', ['Activo', 'Inactivo', 'Potencial'])->default('Activo');
            $table->string('contacto_nombre', 200)->nullable();
            $table->string('contacto_email', 255)->nullable();
            $table->string('contacto_telefono', 50)->nullable();
            $table->string('sitio_web', 500)->nullable();
            $table->text('notas')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index('pais_origen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actores_cooperacion');
    }
};
