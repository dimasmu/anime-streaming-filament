<?php

namespace App\Filament\Resources\VideoUploadTypeResource\Pages;

use App\Filament\Resources\VideoUploadTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoUploadType extends EditRecord
{
    protected static string $resource = VideoUploadTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
