<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoUploadSourceResource\Pages;
use App\Models\VideoUploadSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoUploadSourceResource extends Resource
{
    protected static ?string $model = VideoUploadSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Video Upload Sources';

    protected static ?string $modelLabel = 'Video Upload Source';

    protected static ?string $pluralModelLabel = 'Video Upload Sources';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_video_upload_source') ?? auth()->user()->can('view_video_upload_type');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_video_upload_source') ?? auth()->user()->can('create_video_upload_type');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_video_upload_source') ?? auth()->user()->can('edit_video_upload_type');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_video_upload_source') ?? auth()->user()->can('delete_video_upload_type');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Source Name')
                    ->placeholder('e.g., Google Drive, MediaFire, Mega'),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Source Name'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('episodes_count')
                    ->counts('episodes')
                    ->label('Episodes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_views')
                    ->label('Total Views')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_likes')
                    ->label('Total Likes')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('is_active', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All sources')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
            'index' => Pages\ListVideoUploadSources::route('/'),
            'create' => Pages\CreateVideoUploadSource::route('/create'),
            'edit' => Pages\EditVideoUploadSource::route('/{record}/edit'),
        ];
    }
}
