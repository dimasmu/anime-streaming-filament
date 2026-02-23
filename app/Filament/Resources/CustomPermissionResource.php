<?php

namespace App\Filament\Resources;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource as BasePermissionResource;

class CustomPermissionResource extends BasePermissionResource
{
    protected static ?string $slug = 'permissions';

    protected static ?string $navigationGroup = 'Users Management';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }
}
