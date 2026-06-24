<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Check analisis permissions exist
$perms = Permission::where('name', 'like', 'analisis%')->pluck('name');
echo "=== Permisos analisis ===\n";
echo $perms->toJson() . "\n\n";

// Check pivot table
$ids = Permission::where('name', 'like', 'analisis%')->pluck('id');
$pivot = DB::table('role_has_permissions')
    ->whereIn('permission_id', $ids->toArray())
    ->get();
echo "=== Pivot role_has_permissions ===\n";
echo $pivot->toJson() . "\n\n";

// Check each role
$rolesCheck = ['super_admin', 'admin_provincial', 'editor', 'visualizador', 'publico'];
foreach ($rolesCheck as $roleName) {
    $role = Role::findByName($roleName, 'web');
    $analisisPerms = $role->permissions->where('name', 'like', 'analisis%')->pluck('name')->values();
    echo "{$roleName}: " . $analisisPerms->toJson() . "\n";
}
