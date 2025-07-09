@echo off
echo Setting up anime streaming database...
echo.

echo Running migrations...
php artisan migrate --force
echo.

echo Running seeders...
php artisan db:seed --class=StudioSeeder
echo.

echo Setup completed!
pause