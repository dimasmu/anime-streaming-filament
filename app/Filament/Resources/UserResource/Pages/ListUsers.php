<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create New User')
                ->modalButton('Create User')
                ->modalWidth('lg')
                ->successNotificationTitle('User created successfully')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
