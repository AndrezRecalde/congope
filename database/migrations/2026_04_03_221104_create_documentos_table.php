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
        Schema::create('documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('documentable_type', 100);
            $table->uuid('documentable_id');
            $table->string('titulo', 300);
            $table->enum('categoria', ['Convenio', 'Informe', 'Acta', 'Anexo', 'Normativa', 'Comunicación']);
            $table->string('ruta_archivo', 1000);
            $table->string('nombre_archivo', 300);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('es_publico')->default(false);
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedBigInteger('subido_por')->nullable();
            $table->foreign('subido_por')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id']);
            $table->index('subido_por');
            $table->index('fecha_vencimiento');
            $table->index('es_publico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
