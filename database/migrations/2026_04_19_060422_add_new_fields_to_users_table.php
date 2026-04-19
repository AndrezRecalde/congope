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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono', 15)->after('email')->nullable();
            $table->string('cargo', 255)->after('telefono')->nullable();
            $table->boolean('activo')->default(false)->after('cargo');
            $table->string('entidad', 255)->nullable()->after('activo');
            $table->string('dni', 30)->unique()->nullable()->after('entidad');
            $table->boolean('requires_password_change')->default(false)->after('dni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'cargo',
                'activo',
                'entidad',
                'dni',
                'requires_password_change',
            ]);
        });
    }
};
