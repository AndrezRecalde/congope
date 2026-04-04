<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- CONTEO DE MODELOS ---\n";
echo "User::count() => " . \App\Models\User::count() . "\n";
echo "Role::count() => " . \Spatie\Permission\Models\Role::count() . "\n";
echo "Permission::count() => " . \Spatie\Permission\Models\Permission::count() . "\n";
echo "Provincia::count() => " . \App\Models\Provincia::count() . "\n";
echo "Ods::count() => " . \App\Models\Ods::count() . "\n";

echo "\n--- CREACION DE USUARIO ---\n";
$user = \App\Models\User::updateOrCreate(
    ['email' => 'admin@congope.gob.ec'],
    [
        'name' => 'Super Admin CONGOPE',
        'password' => bcrypt('Admin@2025!')
    ]
);
$user->assignRole('super_admin');

echo "Creado correctamente.\n";
echo "\$user->hasRole('super_admin') => " . ($user->hasRole('super_admin') ? 'true' : 'false') . "\n";
echo "\$user->getAllPermissions()->count() => " . $user->getAllPermissions()->count() . "\n";
echo "\$user->can('proyectos.crear') => " . ($user->can('proyectos.crear') ? 'true' : 'false') . "\n";
echo "\$user->can('usuarios.ver_auditoria') => " . ($user->can('usuarios.ver_auditoria') ? 'true' : 'false') . "\n";

