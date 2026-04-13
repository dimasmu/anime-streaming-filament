<?php

namespace App\Filament\Resources\AnimeResource\Pages;

use App\Filament\Resources\AnimeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnime extends CreateRecord
{
    protected static string $resource = AnimeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If user is EDITOR, set is_published to false (preview mode)
        if (auth()->user()->hasRole('EDITOR')) {
            $data['is_published'] = false;
            $data['is_featured'] = false;
        }

        return $data;
    }
}
