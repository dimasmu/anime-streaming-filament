<?php

namespace App\Filament\Resources\VideoUploadTypeResource\Pages;

use App\Filament\Resources\VideoUploadTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoUploadTypes extends ListRecords
{
    protected static string $resource = VideoUploadTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
