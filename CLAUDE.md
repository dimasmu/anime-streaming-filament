# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an **Anime Streaming Platform** built with **Laravel 12** and **Filament 3** admin panel. The application manages anime content including episodes, genres, categories, studios, and includes a role-based permission system for content management.

### Core Technologies
- **Laravel 12** - PHP framework
- **Filament 3** - Admin panel framework
- **Spatie Permission** - Role-based access control (RBAC)
- **Laravel Media Library** - Media/file management
- **TailwindCSS 4** - Styling via Vite

## Development Commands

### Starting Development Environment
```bash
composer run dev
```
This runs multiple services concurrently:
- Laravel server on port 8090
- Queue worker
- Logs (Pail)
- Vite dev server (port 5173, HMR on localhost)

### Building Assets
```bash
npm run build    # Production build
npm run dev      # Development build with HMR
```

### Testing
```bash
composer test                         # Run all tests (clears config first)
php artisan test                      # Run tests directly
php artisan test --filter TestName    # Run specific test
php artisan test --testsuite=Feature  # Run Feature tests only
php artisan test --testsuite=Unit     # Run Unit tests only
```

### Database Operations
```bash
php artisan migrate                    # Run migrations
php artisan db:seed                    # Run all seeders
php artisan db:seed --class=RolePermissionSeeder  # Seed roles/permissions
```

### Cache & Configuration
```bash
composer run clear-all    # Clear all caches (config, cache, routes, views)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Filament-Specific
```bash
php artisan filament:cache-components   # Cache Filament components
php artisan filament:assets             # Publish Filament assets
php artisan filament:upgrade           # Run after Filament updates (auto-runs on composer dump-autoload)
```

### Laravel Pint (Code Style)
```bash
./vendor/bin/pint    # Laravel Pint code formatter (included in composer dev dependencies)
```

## Project Architecture

### Directory Structure
```
app/
├── Filament/
│   └── Resources/          # Filament admin resources (CRUD interfaces)
│       ├── AnimeResource.php
│       ├── EpisodeResource.php
│       ├── CategoryResource.php
│       ├── GenreResource.php
│       ├── StudioResource.php
│       ├── UserResource.php
│       ├── VideoUploadTypeResource.php
│       ├── CustomRoleResource.php
│       └── CustomPermissionResource.php
├── Http/
│   └── Controllers/       # HTTP controllers
├── Models/                # Eloquent models
│   ├── Anime.php
│   ├── Episode.php
│   ├── Category.php
│   ├── Genre.php
│   ├── Studio.php
│   ├── VideoUploadType.php
│   └── User.php
└── Providers/             # Service providers
```

### Data Model Relationships

**Anime** (central entity)
- `HasMany` → Episodes
- `BelongsTo` → Studio
- `BelongsToMany` → Genres
- `BelongsToMany` → Categories

**Episode**
- `BelongsTo` → Anime
- `BelongsTo` → VideoUploadType

**VideoUploadType**
- `HasMany` → Episodes
- Includes scopes: `active()`, `inactive()`

**Permission System**
Uses Spatie Permission with 4 predefined roles:
- **ADMIN** - Full system access, all permissions
- **MODERATOR** - Content management (can publish/delete content)
- **EDITOR** - Content creation only (cannot publish/delete/bulk operations)
- **VIEWER** - Read-only access

**Permission Naming Convention**
Resources use permissions in format: `{action}_{resource}`
- Examples: `view_anime`, `create_anime`, `edit_anime`, `delete_anime`, `publish_anime`
- Bulk actions: `bulk_delete_{resource}`
- Special permissions: `view_any_anime`, `super_admin`, `manage_system`

### Key Concepts

#### Performance Optimizations
Resources implement eager loading to prevent N+1 queries:
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['studio', 'genres', 'categories'])
        ->withCount('episodes');
}
```

#### Permission-Based UI
Resources check permissions before showing UI elements:
- Create options for relationships (studios, genres, categories) are only shown to users with `create_*` permissions
- Publish toggles only visible to users with `publish_anime` permission
- Permission checks are cached in table methods for performance

#### Media Management
- Images stored in `public/anime/posters` and `public/anime/covers`
- Uses `public` disk configured in `config/filament.php`

#### Auto-generated Slugs
Many resources (Anime, Genre, Category, Studio) use auto-generated slugs from name/title fields:
```php
->live(onBlur: true)
->afterStateUpdated(fn (string $context, $state, callable $set) =>
    $context === 'create' ? $set('slug', Str::slug($state)) : null
)
```
The slug field is typically disabled after creation to maintain URL consistency.

## Configuration

### Environment Setup
1. Copy `.env.example` to `.env`
2. Configure database in `.env`
3. Run `php artisan key:generate`
4. Run migrations and seeders

### Key Config Files
- `config/filament.php` - Filament panel configuration (dark mode, layout)
- `config/permission.php` - Spatie Permission settings
- `config/filament-spatie-roles-permissions.php` - Role/permission admin integration

## Important Notes

### Database Seeders
- `RolePermissionSeeder` - Creates roles, permissions, and assigns admin role to user with email `dimasdemond@gmail.com`
- `GenreSeeder`, `CategorySeeder`, `StudioSeeder` - Seed reference data
- Run `php artisan db:seed --class=RolePermissionSeeder` to reset/recreate permissions

### Storage
- Project includes `fix_storage.bat` for Windows-specific storage issues
- Public disk stores media in `public/anime/posters` and `public/anime/covers`
- Default Filament filesystem disk is `public` (configurable via `FILAMENT_FILESYSTEM_DISK` env var)

### Filament Resources Structure
Each resource follows the pattern:
- Main resource class (e.g., `AnimeResource.php`) - defines form schema, table columns, filters, actions
- `Pages/` subdirectory with:
  - `List{ResourceName}s.php` - index page with table
  - `Create{ResourceName}.php` - create form page
  - `Edit{ResourceName}.php` - edit form page

### Frontend Assets
- Vite handles asset bundling with HMR (Hot Module Replacement)
- Entry points: `resources/css/app.css` and `resources/js/app.js`
- TailwindCSS 4 via Vite plugin
- Filament auto-refreshes when Blade/PHP files change
