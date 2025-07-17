<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpisodeResource\Pages;
use App\Models\Episode;
use App\Models\Anime;
use App\Models\VideoUploadType;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, TextInput, Select, Textarea, FileUpload, DatePicker, Toggle};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, ImageColumn, BadgeColumn, ToggleColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Actions\{EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction};
use Illuminate\Database\Eloquent\Builder;

class EpisodeResource extends Resource
{
    protected static ?string $model = Episode::class;

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return auth()->user()->hasRole('ADMIN');
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_episode');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_episode');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_episode');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_episode');
    }

    protected static ?string $navigationIcon = 'heroicon-o-play';

    protected static ?string $navigationGroup = 'Content Management';

    // PERFORMANCE FIX: Add eager loading to prevent N+1 queries
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['anime', 'videoUploadType']); // Eager load relationships
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Episode Information')
                    ->schema([
                        // PERFORMANCE FIX: Remove preload() for better performance
                        Select::make('anime_id')
                            ->relationship('anime', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->required(),

                        TextInput::make('episode_number')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        DatePicker::make('air_date'),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('episodes/thumbnails'),

                        TextInput::make('video_url')
                            ->url()
                            ->placeholder('https://example.com/video.mp4'),

                        Select::make('video_upload_type_id')
                            ->label('Video Upload Type')
                            ->relationship('videoUploadType', 'name')
                            ->options(VideoUploadType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Select upload type')
                            ->createOptionForm(auth()->user()->can('create_video_upload_type') ? [
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., YouTube, Vimeo, Direct Upload'),
                                Textarea::make('description')
                                    ->placeholder('Optional description of this upload type')
                                    ->rows(3),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Only active upload types will be available for selection'),
                            ] : null)
                            ->helperText(auth()->user()->can('create_video_upload_type') ? null : 'Contact admin to add new video upload types'),

                        TextInput::make('duration')
                            ->numeric()
                            ->suffix('minutes'),
                    ])->columns(3),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')->visible(fn() => auth()->user()->can('publish_episode'))
                            ->helperText(fn() => auth()->user()->hasRole('EDITOR') ? 'Only admins can publish content' : null),
                    ])->visible(fn() => auth()->user()->can('publish_episode')),

                Section::make('Statistics')
                    ->schema([
                        TextInput::make('likes')
                            ->numeric()
                            ->disabled()
                            ->default(0)
                            ->helperText('Number of likes (read-only)'),

                        TextInput::make('views')
                            ->numeric()
                            ->disabled()
                            ->default(0)
                            ->helperText('Number of views (read-only)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Cache user permissions to avoid repeated checks
        $canPublish = auth()->user()->can('publish_episode');
        $canDelete = auth()->user()->can('delete_episode');

        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->square()
                    ->label('Cover')
                    ->defaultImageUrl(function () {
                        return asset('images/no-images.png');
                    })
                    ->extraImgAttributes(['alt' => 'Cover'])
                    ->checkFileExistence(false)
                    ->size(60),

                TextColumn::make('anime.title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('episode_number')
                    ->label('Episode #')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('videoUploadType.name')
                    ->label('Upload Type')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('duration')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('air_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('likes')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->visible($canPublish),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // PERFORMANCE FIX: Remove preload() from filters
                SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->searchable(),

                SelectFilter::make('video_upload_type')
                    ->relationship('videoUploadType', 'name')
                    ->searchable(),

                TernaryFilter::make('is_published'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('anime_id', 'asc')
            ->defaultSort('episode_number', 'asc');
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
            'index' => Pages\ListEpisodes::route('/'),
            'create' => Pages\CreateEpisode::route('/create'),
            'edit' => Pages\EditEpisode::route('/{record}/edit'),
        ];
    }
}
