<?php

namespace App\Filament\Resources\StudioResource\Pages;

use App\Filament\Resources\StudioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudio extends CreateRecord
{
    protected static string $resource = StudioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}