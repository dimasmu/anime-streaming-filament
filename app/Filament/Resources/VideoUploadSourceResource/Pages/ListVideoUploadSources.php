<?php

namespace App\Filament\Resources\VideoUploadSourceResource\Pages;

use App\Filament\Resources\VideoUploadSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoUploadSources extends ListRecords
{
    protected static string $resource = VideoUploadSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
