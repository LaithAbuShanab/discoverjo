<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $permissions = [
            'view_any_admin',
            'view_admin',
            'create_admin',
            'update_admin',
            'delete_admin',
            'delete_any_admin',
            'force_delete_admin',
            'force_delete_any_admin',
            'restore_admin',
            'restore_any_admin',
            'replicate_admin',
            'reorder_admin',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles (optional)
        $roles = ['Super Admin', 'Admin Manager'];
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Assign all permissions to the Super Admin role
            if ($roleName === 'Super Admin') {
                $role->syncPermissions($permissions);
            }
        }
    }
}
