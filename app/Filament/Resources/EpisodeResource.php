<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpisodeResource\Pages;
use App\Models\Episode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
            ->with(['anime', 'videoUploadSource']); // Eager load relationships
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Episode Information')
                    ->schema([
                        // PERFORMANCE FIX: Remove preload() for better performance
                        Forms\Components\Select::make('anime_id')
                            ->relationship('anime', 'title')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->required(),

                        Forms\Components\TextInput::make('episode_number')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('air_date'),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('episodes/thumbnails'),

                        Forms\Components\TextInput::make('video_url')
                            ->url()
                            ->placeholder('https://example.com/video.mp4'),

                        Forms\Components\Select::make('video_upload_source_id')
                            ->label('Video Upload Source')
                            ->relationship('videoUploadSource', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select upload source (e.g., Google Drive, MediaFire)'),

                        Forms\Components\Select::make('quality')
                            ->options([
                                '360' => '360p',
                                '480' => '480p (SD)',
                                '720' => '720p (HD)',
                                '1080' => '1080p (Full HD)',
                            ])
                            ->label('Video Quality')
                            ->placeholder('Select quality')
                            ->default('720')
                            ->required(),

                        Forms\Components\TextInput::make('duration')
                            ->numeric()
                            ->suffix('minutes'),
                    ])->columns(4),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')->visible(fn () => auth()->user()->can('publish_episode'))
                            ->helperText(fn () => auth()->user()->hasRole('EDITOR') ? 'Only admins can publish content' : null),
                    ])->visible(fn () => auth()->user()->can('publish_episode')),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('likes')
                            ->numeric()
                            ->disabled()
                            ->default(0)
                            ->helperText('Number of likes (read-only)'),

                        Forms\Components\TextInput::make('views')
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
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->size(60),

                Tables\Columns\TextColumn::make('anime.title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('episode_number')
                    ->label('Episode #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('videoUploadSource.name')
                    ->label('Source')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('quality')
                    ->colors([
                        'danger' => '360',
                        'gray' => '480',
                        'primary' => '720',
                        'success' => '1080',
                    ])
                    ->formatStateUsing(fn ($state) => $state.'p')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('air_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('likes')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Published')
                    ->visible($canPublish),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('quality')
                    ->options([
                        '360' => '360p',
                        '480' => '480p',
                        '720' => '720p',
                        '1080' => '1080p',
                    ])
                    ->placeholder('All Qualities'),

                Tables\Filters\SelectFilter::make('video_upload_source')
                    ->relationship('videoUploadSource', 'name')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
