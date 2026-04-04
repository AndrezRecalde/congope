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
        Schema::create('redes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre', 300);
            $table->enum('tipo', ['Regional', 'Nacional', 'Internacional', 'Temática']);
            $table->text('objetivo')->nullable();
            $table->enum('rol_congope', ['Miembro', 'Coordinador', 'Observador'])->default('Miembro');
            $table->date('fecha_adhesion')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tipo');
            $table->index('rol_congope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redes');
    }
};
