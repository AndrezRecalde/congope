<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear 5 roles (guard: 'web')
        $roles = [
            'super_admin',
            'admin_provincial',
            'editor',
            'visualizador',
            'publico'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 3. Crear 52 permisos agrupados por módulo
        $permissions = [
            'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'asignar_rol', 'asignar_provincia', 'ver_auditoria'],
            'actores' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
            'proyectos' => ['ver', 'crear', 'editar', 'cambiar_estado', 'eliminar', 'exportar', 'ver_todas_provincias'],
            'hitos' => ['crear', 'editar', 'completar'],
            'practicas' => ['ver', 'crear', 'editar', 'eliminar', 'destacar', 'valorar', 'exportar'],
            'redes' => ['ver', 'crear', 'editar', 'eliminar', 'gestionar_miembros'],
            'emblematicos' => ['ver', 'crear', 'editar', 'eliminar', 'publicar'],
            'reconocimientos' => ['crear', 'editar', 'eliminar'],
            'documentos' => ['ver', 'ver_confidencial', 'subir', 'editar', 'eliminar', 'publicar', 'ver_todas_provincias'],
            'eventos' => ['ver', 'crear', 'editar', 'eliminar', 'gestionar_participantes'],
            'compromisos' => ['crear', 'resolver'],
            'mapa' => ['ver', 'ver_todas_capas', 'exportar'],
            'dashboard' => ['ver', 'ver_global'],
            'reportes' => ['generar', 'exportar_masivo'],
        ];

        $flattenedPermissions = [];
        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                $flattenedPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // 4. Asignar permisos a roles según matriz RBAC
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->givePermissionTo(Permission::all());

        $adminProvincialExcluded = [
            'usuarios.crear', 'usuarios.eliminar', 'usuarios.asignar_rol', 'usuarios.asignar_provincia', 'usuarios.ver_auditoria',
            'actores.eliminar',
            'proyectos.eliminar', 'proyectos.ver_todas_provincias',
            'practicas.eliminar', 'practicas.destacar',
            'redes.crear', 'redes.editar', 'redes.eliminar', 'redes.gestionar_miembros',
            'emblematicos.crear', 'emblematicos.editar', 'emblematicos.eliminar', 'emblematicos.publicar',
            'reconocimientos.crear', 'reconocimientos.editar', 'reconocimientos.eliminar',
            'documentos.eliminar', 'documentos.publicar', 'documentos.ver_todas_provincias',
            'eventos.eliminar',
            'mapa.ver_todas_capas',
            'dashboard.ver_global',
            'reportes.exportar_masivo'
        ];
        
        $adminProvincial = Role::where('name', 'admin_provincial')->first();
        $adminProvincialPermissions = array_diff($flattenedPermissions, $adminProvincialExcluded);
        $adminProvincial->givePermissionTo($adminProvincialPermissions);

        $editor = Role::where('name', 'editor')->first();
        $editor->givePermissionTo([
            'actores.ver', 'actores.crear', 'actores.editar', 'actores.exportar',
            'proyectos.ver', 'proyectos.crear', 'proyectos.editar', 'proyectos.exportar',
            'hitos.crear', 'hitos.editar', 'hitos.completar',
            'practicas.ver', 'practicas.crear', 'practicas.editar', 'practicas.valorar', 'practicas.exportar',
            'redes.ver',
            'emblematicos.ver',
            'documentos.ver', 'documentos.subir', 'documentos.editar',
            'eventos.ver', 'eventos.crear', 'eventos.editar', 'eventos.gestionar_participantes',
            'compromisos.crear', 'compromisos.resolver',
            'mapa.ver', 'mapa.exportar',
            'dashboard.ver', 'reportes.generar', 'usuarios.ver'
        ]);

        $visualizador = Role::where('name', 'visualizador')->first();
        $visualizador->givePermissionTo([
            'actores.ver', 'actores.exportar',
            'proyectos.ver', 'proyectos.exportar', 'proyectos.ver_todas_provincias',
            'practicas.ver', 'practicas.exportar',
            'redes.ver',
            'emblematicos.ver',
            'documentos.ver',
            'eventos.ver',
            'mapa.ver', 'mapa.ver_todas_capas', 'mapa.exportar',
            'dashboard.ver', 'dashboard.ver_global',
            'reportes.generar'
        ]);

        $publico = Role::where('name', 'publico')->first();
        $publico->givePermissionTo([
            'practicas.ver', 'emblematicos.ver',
            'mapa.ver', 'reportes.generar'
        ]);
    }
}
