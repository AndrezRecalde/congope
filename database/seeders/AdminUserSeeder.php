<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@congope.gob.ec'],
            [
                'name' => 'Super Admin CONGOPE',
                'password' => Hash::make('Admin@2025!'),
            ]
        );

        // Validar si el rol super_admin existe y asignarlo
        if (Role::where('name', 'super_admin')->exists()) {
            $superAdmin->assignRole('super_admin');
        } else {
            $role = Role::create(['name' => 'super_admin']);
            $superAdmin->assignRole($role);
        }
    }
}
