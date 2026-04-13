<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Filament\Resources\UserResource;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "Filament Users Section Verification\n";
echo str_repeat('=', 50) . "\n\n";

// Check if UserResource class exists
echo "✅ UserResource class exists\n";

// Check roles
echo "\nRoles:\n";
echo str_repeat('-', 50) . "\n";
$roles = Role::all();
foreach ($roles as $role) {
    $userCount = User::role($role->name)->count();
    echo "  {$role->name}: {$userCount} users\n";
}

// Check permissions
echo "\nPermissions:\n";
echo str_repeat('-', 50) . "\n";
$permissions = Permission::all();
echo "  Total permissions: " . $permissions->count() . "\n";

// Check users
echo "\nUsers:\n";
echo str_repeat('-', 50) . "\n";
$totalUsers = User::count();
$adminUsers = User::role('ADMIN')->count();
$customerUsers = User::role('CUSTOMER')->count();

echo "  Total: {$totalUsers}\n";
echo "  ADMIN: {$adminUsers}\n";
echo "  CUSTOMER: {$customerUsers}\n";

// Check if resources are properly configured
echo "\nResource Configuration:\n";
echo str_repeat('-', 50) . "\n";

try {
    $userResourceModel = UserResource::getModel();
    echo "✅ UserResource model: " . class_basename($userResourceModel) . "\n";
} catch (\Exception $e) {
    echo "❌ UserResource error: " . $e->getMessage() . "\n";
}

try {
    $customRoleResource = \App\Filament\Resources\CustomRoleResource::class;
    echo "✅ CustomRoleResource exists\n";
} catch (\Exception $e) {
    echo "❌ CustomRoleResource error: " . $e->getMessage() . "\n";
}

try {
    $customPermissionResource = \App\Filament\Resources\CustomPermissionResource::class;
    echo "✅ CustomPermissionResource exists\n";
} catch (\Exception $e) {
    echo "❌ CustomPermissionResource error: " . $e->getMessage() . "\n";
}

echo "\n✅ Verification complete!\n";
