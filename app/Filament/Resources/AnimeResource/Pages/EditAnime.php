<?php

namespace App\Filament\Resources\AnimeResource\Pages;

use App\Filament\Resources\AnimeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnime extends EditRecord
{
    protected static string $resource = AnimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('delete_anime')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If user is EDITOR, ensure they can't publish content
        if (auth()->user()->hasRole('EDITOR')) {
            // Keep the original published status if it was already published
            // But don't allow editors to publish new content
            if (!$this->record->is_published) {
                $data['is_published'] = false;
            }
            $data['is_featured'] = $this->record->is_featured; // Keep original featured status
        }

        return $data;
    }
}
