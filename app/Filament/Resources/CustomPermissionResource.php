<?php

namespace App\Filament\Resources;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource as BasePermissionResource;

class CustomPermissionResource extends BasePermissionResource
{
    protected static ?string $slug = 'permissions';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }
}