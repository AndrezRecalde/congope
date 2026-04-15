<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cantones
        Schema::create('cantones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provincia_id')->constrained('provincias')->cascadeOnDelete();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->timestamps();
        });

        // 2. Parroquias
        Schema::create('parroquias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('canton_id')->constrained('cantones')->cascadeOnDelete();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 150);
            $table->timestamps();
        });

        // 3. Proyecto -> Múltiples Ubicaciones Exactas (GIS), vinculadas a un cantón
        Schema::create('proyecto_ubicaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignUuid('canton_id')->constrained('cantones')->cascadeOnDelete();
            $table->string('nombre', 255)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE proyecto_ubicaciones ADD COLUMN ubicacion geometry(Point, 4326)');
        DB::statement('CREATE INDEX idx_proyecto_ubicaciones_geom ON proyecto_ubicaciones USING GIST (ubicacion)');
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_ubicaciones');
        Schema::dropIfExists('parroquias');
        Schema::dropIfExists('cantones');
    }
};
