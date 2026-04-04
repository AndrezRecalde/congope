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
        Schema::create('proyecto_ods', function (Blueprint $table) {
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('ods_id')->constrained('ods')->cascadeOnDelete();

            $table->primary(['proyecto_id', 'ods_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_ods');
    }
};
