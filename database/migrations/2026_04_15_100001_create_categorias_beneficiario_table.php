<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_beneficiario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('grupo', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('grupo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_beneficiario');
    }
};
