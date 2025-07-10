<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_anime',
            'create_anime',
            'edit_anime',
            'delete_anime',
            'publish_anime',
            'view_dashboard',
            'view_all_resources',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create ADMIN role
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $adminRole->givePermissionTo(Permission::all());

        // Create EDITOR role
        $editorRole = Role::firstOrCreate(['name' => 'EDITOR']);
        $editorRole->givePermissionTo([
            'view_anime',
            'create_anime',
            'edit_anime',
            'view_dashboard',
        ]);

        // Assign ADMIN role to existing dimasmu user
        $dimasUser = User::where('email', 'dimasdemond@gmail.com')->first();
        if ($dimasUser) {
            $dimasUser->assignRole('ADMIN');
            echo "Assigned ADMIN role to dimasmu (dimasdemond@gmail.com)\n";
        }

        // Create additional admin user if doesn't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $adminUser->assignRole('ADMIN');

        // Create editor users
        $editorUsers = [
            [
                'name' => 'Sarah Editor',
                'email' => 'sarah@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'John Editor',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Maria Editor',
                'email' => 'maria@example.com',
                'password' => bcrypt('password'),
            ]
        ];

        foreach ($editorUsers as $userData) {
            $editorUser = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $editorUser->assignRole('EDITOR');
        }

        echo "Roles and permissions setup completed!\n";
        echo "ADMIN users: dimasmu, admin@example.com\n";
        echo "EDITOR users: sarah@example.com, john@example.com, maria@example.com\n";
    }
}