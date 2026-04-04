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
        Schema::create('ods', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('numero')->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->string('icono_url', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ods');
    }
};
