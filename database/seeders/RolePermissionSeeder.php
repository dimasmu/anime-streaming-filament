<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

            // Video Upload Source Management permissions
            'view_video_upload_source',
            'create_video_upload_source',
            'edit_video_upload_source',
            'delete_video_upload_source',
            'bulk_delete_video_upload_source',

            // Comment Management permissions
            'view_any_comment',
            'view_comment',
            'edit_comment',
            'delete_comment',

            // Bookmark Management permissions
            'view_any_bookmark',
            'view_bookmark',
            'edit_bookmark',
            'delete_bookmark',

            // API permissions for VIEWER role
            'api_access',
            'create_comment',
            'update_own_comment',
            'delete_own_comment',
            'create_bookmark',
            'update_own_bookmark',
            'delete_own_bookmark',

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

            // Category permissions (view and create only)
            'view_category',
            'create_category',
            'edit_category',

            // Genre permissions (view and create only)
            'view_genre',
            'create_genre',
            'edit_genre',

            // Studio permissions (view and create only)
            'view_studio',
            'create_studio',
            'edit_studio',

            // Video Upload Type permissions (view only)
            'view_video_upload_type',

            // Media management
            'manage_media',
            'upload_files',
            'view_file_manager',
        ];

        $editorRole->givePermissionTo($editorPermissions);

        // Create VIEWER role with read-only permissions + API access
        $viewerRole = Role::firstOrCreate(['name' => 'VIEWER']);
        $viewerPermissions = [
            'view_dashboard',
            'view_anime',
            'view_any_anime',
            'view_episode',
            'view_category',
            'view_genre',
            'view_studio',
            'view_video_upload_source',
            // API access for comments and bookmarks
            'api_access',
            'create_comment',
            'update_own_comment',
            'delete_own_comment',
            'create_bookmark',
            'update_own_bookmark',
            'delete_own_bookmark',
        ];

        $viewerRole->givePermissionTo($viewerPermissions);

        // Create MODERATOR role with content management permissions
        $moderatorRole = Role::firstOrCreate(['name' => 'MODERATOR']);
        $moderatorPermissions = [
            // Dashboard access
            'view_dashboard',
            'view_analytics',

            // Full anime management except system-level operations
            'view_anime',
            'create_anime',
            'edit_anime',
            'delete_anime',
            'publish_anime',
            'unpublish_anime',
            'view_any_anime',

            // Full episode management
            'view_episode',
            'create_episode',
            'edit_episode',
            'delete_episode',
            'publish_episode',
            'unpublish_episode',

            // Full category management
            'view_category',
            'create_category',
            'edit_category',
            'delete_category',

            // Full genre management
            'view_genre',
            'create_genre',
            'edit_genre',
            'delete_genre',

            // Full studio management
            'view_studio',
            'create_studio',
            'edit_studio',
            'delete_studio',
            'activate_studio',
            'deactivate_studio',

            // Full video upload source management
            'view_video_upload_source',
            'create_video_upload_source',
            'edit_video_upload_source',
            'delete_video_upload_source',

            // Comment management (moderate user comments)
            'view_any_comment',
            'edit_comment',
            'delete_comment',

            // Bookmark management (view user bookmarks)
            'view_any_bookmark',
            'view_bookmark',

            // Media management
            'manage_media',
            'upload_files',
            'delete_files',
            'view_file_manager',
        ];

        $moderatorRole->givePermissionTo($moderatorPermissions);

        // Assign ADMIN role to the seeded user
        $user = User::where('email', 'dimasdemond@gmail.com')->first();
        if ($user) {
            $user->assignRole('ADMIN');
        }

        $this->command->info('Roles and permissions have been seeded successfully!');
        $this->command->info('Created roles: ADMIN, EDITOR, VIEWER, MODERATOR');
        $this->command->info('User dimasdemond@gmail.com has been assigned ADMIN role');
    }
}
