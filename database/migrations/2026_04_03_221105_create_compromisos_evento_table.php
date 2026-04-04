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
        Schema::create('compromisos_evento', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->string('descripcion', 500);
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->foreign('responsable_id')->references('id')->on('users')->nullOnDelete();
            $table->date('fecha_limite');
            $table->boolean('resuelto')->default(false);
            $table->date('resuelto_en')->nullable();
            $table->timestamps();

            $table->index('evento_id');
            $table->index('responsable_id');
            $table->index('fecha_limite');
            $table->index('resuelto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compromisos_evento');
    }
};
