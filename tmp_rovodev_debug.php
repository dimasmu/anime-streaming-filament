<?php
// Debug script to check if VideoUploadType resource is being discovered
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check if the resource class exists and can be instantiated
try {
    $resourceClass = 'App\Filament\Resources\VideoUploadTypeResource';
    echo "Checking if $resourceClass exists: " . (class_exists($resourceClass) ? 'YES' : 'NO') . "\n";
    
    if (class_exists($resourceClass)) {
        echo "Resource model: " . $resourceClass::getModel() . "\n";
        echo "Resource slug: " . $resourceClass::getSlug() . "\n";
        
        // Check if user can view
        if (auth()->check()) {
            echo "User can view: " . ($resourceClass::canViewAny() ? 'YES' : 'NO') . "\n";
        } else {
            echo "No authenticated user\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}