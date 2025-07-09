# Run Laravel migrations
Write-Host "Running Laravel migrations..." -ForegroundColor Green

# Check if we can find artisan
if (Test-Path "artisan") {
    Write-Host "Found artisan file" -ForegroundColor Yellow
    
    # Try to run migrations using different methods
    try {
        # Method 1: Try with php command
        Write-Host "Attempting to run migrations with php..." -ForegroundColor Yellow
        & php artisan migrate --force
    }
    catch {
        Write-Host "PHP command not found, trying alternative methods..." -ForegroundColor Red
        
        # Method 2: Try to find PHP in common locations
        $phpPaths = @(
            "C:\php\php.exe",
            "C:\xampp\php\php.exe",
            "C:\wamp64\bin\php\php8.2.0\php.exe",
            "C:\laragon\bin\php\php8.2.0\php.exe"
        )
        
        $phpFound = $false
        foreach ($path in $phpPaths) {
            if (Test-Path $path) {
                Write-Host "Found PHP at: $path" -ForegroundColor Green
                & $path artisan migrate --force
                $phpFound = $true
                break
            }
        }
        
        if (-not $phpFound) {
            Write-Host "PHP not found. Please run manually: php artisan migrate" -ForegroundColor Red
        }
    }
} else {
    Write-Host "artisan file not found. Make sure you're in the Laravel project root." -ForegroundColor Red
}

Write-Host "Migration script completed." -ForegroundColor Green