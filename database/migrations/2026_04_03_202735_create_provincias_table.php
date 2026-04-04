<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provincias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre', 150);
            $table->string('codigo', 10)->unique();
            $table->string('capital', 150);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE provincias ADD COLUMN geom geometry(MultiPolygon, 4326);');
        DB::statement('CREATE INDEX idx_provincias_geom ON provincias USING GIST (geom);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_provincias_geom;');
        Schema::dropIfExists('provincias');
    }
};
