<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->enum('flujo_direccion', ['Norte-Sur', 'Sur-Sur', 'Triangular', 'Interna', 'Descentralizada'])->nullable()->after('sector_tematico');
            $table->json('modalidad_cooperacion')->nullable()->after('flujo_direccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['flujo_direccion', 'modalidad_cooperacion']);
        });
    }
};
