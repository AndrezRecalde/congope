<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Los permisos de análisis se crean en una
     * migración (no solo en seeder) para que
     * existan en todos los entornos sin necesidad
     * de ejecutar seeders manualmente.
     */
    public function up(): void
    {
        // Limpiar caché de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // ── Crear permisos nuevos ─────────────
        $guard = 'web'; // Mismo guard del sistema

        $permisosNuevos = [
            'analisis.ver',
            'analisis.ver_global',
            'analisis.exportar',
        ];

        foreach ($permisosNuevos as $nombre) {
            Permission::firstOrCreate([
                'name'       => $nombre,
                'guard_name' => $guard,
            ]);
        }

        // ── Asignar a roles ───────────────────

        // super_admin: todos los permisos de análisis
        $superAdmin = Role::findByName('super_admin', $guard);
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'analisis.ver',
                'analisis.ver_global',
                'analisis.exportar',
            ]);
        }

        // admin_provincial: ver y exportar
        // NO tiene ver_global (solo ve sus provincias)
        $adminProvincial = Role::findByName('admin_provincial', $guard);
        if ($adminProvincial) {
            $adminProvincial->givePermissionTo([
                'analisis.ver',
                'analisis.exportar',
            ]);
        }

        // editor: solo ver
        $editor = Role::findByName('editor', $guard);
        if ($editor) {
            $editor->givePermissionTo(['analisis.ver']);
        }

        // visualizador: solo ver
        $visualizador = Role::findByName('visualizador', $guard);
        if ($visualizador) {
            $visualizador->givePermissionTo(['analisis.ver']);
        }

        // publico: ningún permiso de análisis
        // (no se asigna nada)

        // Limpiar caché nuevamente después de asignar permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // Revocar de todos los roles primero
        $guard = 'web';
        $roles = [
            'super_admin',
            'admin_provincial',
            'editor',
            'visualizador',
        ];

        foreach ($roles as $nombreRol) {
            $rol = Role::findByName($nombreRol, $guard);
            if ($rol) {
                $rol->revokePermissionTo([
                    'analisis.ver',
                    'analisis.ver_global',
                    'analisis.exportar',
                ]);
            }
        }

        // Eliminar los permisos
        Permission::whereIn('name', [
            'analisis.ver',
            'analisis.ver_global',
            'analisis.exportar',
        ])->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();
    }
};
