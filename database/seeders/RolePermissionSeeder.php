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
        // Create all possible permissions for all resources
        $permissions = [
            // User Management permissions
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',
            'bulk_delete_user',
            
            // Role & Permission Management permissions
            'view_role',
            'create_role',
            'edit_role',
            'delete_role',
            'bulk_delete_role',
            'view_permission',
            'create_permission',
            'edit_permission',
            'delete_permission',
            'bulk_delete_permission',
            
            // Anime Management permissions
            'view_anime',
            'create_anime',
            'edit_anime',
            'delete_anime',
            'bulk_delete_anime',
            'publish_anime',
            'unpublish_anime',
            'view_any_anime',
            
            // Episode Management permissions
            'view_episode',
            'create_episode',
            'edit_episode',
            'delete_episode',
            'bulk_delete_episode',
            'publish_episode',
            'unpublish_episode',
            
            // Category Management permissions
            'view_category',
            'create_category',
            'edit_category',
            'delete_category',
            'bulk_delete_category',
            
            // Genre Management permissions
            'view_genre',
            'create_genre',
            'edit_genre',
            'delete_genre',
            'bulk_delete_genre',
            
            // Studio Management permissions
            'view_studio',
            'create_studio',
            'edit_studio',
            'delete_studio',
            'bulk_delete_studio',
            'activate_studio',
            'deactivate_studio',
            
            // Dashboard & System permissions
            'view_dashboard',
            'view_analytics',
            'view_reports',
            'export_data',
            'import_data',
            'manage_settings',
            'view_logs',
            'clear_cache',
            
            // Content Management permissions
            'manage_media',
            'upload_files',
            'delete_files',
            'view_file_manager',
            
            // Advanced permissions
            'view_all_resources',
            'super_admin',
            'manage_system',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create ADMIN role with ALL permissions
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        $adminRole->givePermissionTo(Permission::all());

        // Create EDITOR role with limited permissions
        // Editor can only view and create animes/episodes, but cannot publish or delete
        $editorRole = Role::firstOrCreate(['name' => 'EDITOR']);
        $editorPermissions = [
            // Basic dashboard access
            'view_dashboard',
            
            // Anime permissions (view and create only, no publish/delete)
            'view_anime',
            'create_anime',
            'edit_anime',
            'view_any_anime',
            
            // Episode permissions (view and create only, no publish/delete)
            'view_episode',
            'create_episode',
            'edit_episode',
            
            // Basic content viewing permissions
            'view_category',
            'view_genre',
            'view_studio',
        ];
        $editorRole->givePermissionTo($editorPermissions);

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

        echo "\nRoles and Permissions Setup Completed!\n";
        echo "Created " . count($permissions) . " permissions\n";
        echo "Created 2 roles: ADMIN and EDITOR\n";
        echo "\nRole Permissions Summary:\n";
        echo "ADMIN: Has ALL permissions (including publish/delete)\n";
        echo "EDITOR: Can view and create animes/episodes only (NO publish/delete permissions)\n";
        echo "\nUsers Created:\n";
        echo "ADMIN users: dimasmu (dimasdemond@gmail.com), admin@example.com\n";
        echo "EDITOR users: sarah@example.com, john@example.com, maria@example.com\n";
        echo "\nPublishing and deleting animes/episodes is restricted to ADMIN users only.\n";
    }
}