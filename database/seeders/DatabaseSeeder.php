<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            'view sites',
            'create sites',
            'edit sites',
            'delete sites',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Admin role gets everything
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Create the analytics user
        $user = User::firstOrCreate(
            ['email' => 'thephpx@gmail.com'],
            [
                'name'     => 'ThePhpX',
                'password' => bcrypt('Faisal123'),
            ]
        );

        $user->assignRole('admin');
    }
}
