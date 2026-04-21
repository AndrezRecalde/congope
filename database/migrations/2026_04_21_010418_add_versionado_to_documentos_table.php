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
        Schema::table('documentos', function (Blueprint $table) {
            // ID del documento padre (v1).
            // null en el documento original.
            // En v2, v3, etc. → apunta a la v1.
            $table->uuid('documento_padre_id')
                ->nullable()
                ->after('version');

            $table->foreign('documento_padre_id')
                ->references('id')
                ->on('documentos')
                ->onDelete('set null');

            // Solo la versión más reciente es "activa"
            // y aparece en el listado principal.
            // Las versiones anteriores quedan como
            // historial accesible pero no visibles
            // en el listado estándar.
            $table->boolean('version_activa')
                ->default(true)
                ->after('documento_padre_id');

            // Índice para consultas frecuentes
            $table->index([
                'documento_padre_id',
                'version_activa',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['documento_padre_id']);
            $table->dropIndex(['documento_padre_id', 'version_activa']);
            $table->dropColumn(['documento_padre_id', 'version_activa']);
        });
    }
};
