<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoUploadTypeResource\Pages;
use App\Models\VideoUploadType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoUploadTypeResource extends Resource
{
    protected static ?string $model = VideoUploadType::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Video Upload Types';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_video_upload_type');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_video_upload_type');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_video_upload_type');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_video_upload_type');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoUploadTypes::route('/'),
            'create' => Pages\CreateVideoUploadType::route('/create'),
            'edit' => Pages\EditVideoUploadType::route('/{record}/edit'),
        ];
    }
}
