<?php

namespace App\Filament\Resources;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource as BaseRoleResource;

class CustomRoleResource extends BaseRoleResource
{
    protected static ?string $slug = 'roles';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }
}