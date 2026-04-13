<?php

namespace App\Filament\Resources\VideoUploadSourceResource\Pages;

use App\Filament\Resources\VideoUploadSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoUploadSource extends EditRecord
{
    protected static string $resource = VideoUploadSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
