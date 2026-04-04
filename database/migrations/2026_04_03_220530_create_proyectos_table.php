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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 500);
            $table->foreignUuid('actor_id')->constrained('actores_cooperacion')->restrictOnDelete();
            $table->enum('estado', ['En gestión', 'En ejecución', 'Finalizado', 'Suspendido'])->default('En gestión');
            $table->decimal('monto_total', 15, 2)->nullable();
            $table->string('moneda', 3)->default('USD');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin_planificada')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->unsignedTinyInteger('porcentaje_avance')->default(0);
            $table->unsignedInteger('beneficiarios_directos')->nullable();
            $table->unsignedInteger('beneficiarios_indirectos')->nullable();
            $table->string('sector_tematico', 150)->nullable();
            
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();

            $table->index('estado');
            $table->index('actor_id');
            $table->index('creado_por');
            $table->index('fecha_inicio');
        });

        DB::statement('ALTER TABLE proyectos ADD COLUMN ubicacion geometry(Point, 4326)');
        DB::statement('CREATE INDEX idx_proyectos_ubicacion ON proyectos USING GIST (ubicacion)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
