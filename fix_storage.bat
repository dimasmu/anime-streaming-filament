@echo off
echo Creating storage link...
php artisan storage:link

echo.
echo Creating storage directories...
if not exist "storage\app\public\anime" mkdir "storage\app\public\anime"
if not exist "storage\app\public\anime\posters" mkdir "storage\app\public\anime\posters"
if not exist "storage\app\public\anime\covers" mkdir "storage\app\public\anime\covers"

echo.
echo Moving files from public to storage (if they exist)...
if exist "public\anime\posters\*.*" (
    echo Moving poster images...
    move "public\anime\posters\*.*" "storage\app\public\anime\posters\"
)

if exist "public\anime\covers\*.*" (
    echo Moving cover images...
    move "public\anime\covers\*.*" "storage\app\public\anime\covers\"
)

echo.
echo Done! Your images should now be accessible via the storage link.
pause