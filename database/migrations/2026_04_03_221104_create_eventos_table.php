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
        Schema::create('eventos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('titulo', 300);
            $table->enum('tipo_evento', ['Misión técnica', 'Reunión bilateral', 'Conferencia', 'Visita de campo', 'Virtual', 'Otro']);
            $table->date('fecha_evento');
            $table->string('lugar', 300)->nullable();
            $table->boolean('es_virtual')->default(false);
            $table->string('url_virtual', 500)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('fecha_evento');
            $table->index('tipo_evento');
            $table->index('creado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
