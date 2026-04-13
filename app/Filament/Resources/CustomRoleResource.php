<?php

namespace App\Filament\Resources;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource as BaseRoleResource;

class CustomRoleResource extends BaseRoleResource
{
    protected static ?string $slug = 'roles';

    protected static ?string $navigationGroup = 'Users Management';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }
}
