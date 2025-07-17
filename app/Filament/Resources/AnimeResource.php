<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Models\Anime;
use App\Models\VideoUploadType;
use Filament\Forms\Form;
use Filament\Forms\Components\{ColorPicker, Section, TextInput, Select, Textarea, RichEditor, FileUpload, DatePicker, Toggle};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, ImageColumn, BadgeColumn, ToggleColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Actions\{EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AnimeResource extends Resource
{
    protected static ?string $model = Anime::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Content Management';

    // Add pagination for better performance
    protected static ?int $recordsPerPage = 25;

    // PERFORMANCE FIX: Add eager loading to prevent N+1 queries
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['studio', 'genres', 'categories', 'videoUploadType']) // Eager load relationships
            ->withCount('episodes as actual_episodes_count'); // Add actual episode count with different name
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_anime');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_anime');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_anime');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_anime');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, callable $set, callable $get) {
                                if ($context === 'create' || empty($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from title'),

                        Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'hiatus' => 'Hiatus',
                            ])
                            ->required(),

                        Select::make('type')
                            ->options([
                                'tv' => 'TV Series',
                                'movie' => 'Movie',
                                'ova' => 'OVA',
                                'ona' => 'ONA',
                                'special' => 'Special',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3),

                        RichEditor::make('synopsis')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('poster_image')
                            ->image()
                            ->disk('public')
                            ->directory('anime/posters')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '2:3',
                                '3:4',
                                '1:1',
                            ])
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Upload a poster image (max 5MB). Recommended size: 600x900px'),

                        FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('anime/covers')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Upload a cover image (max 5MB). Recommended size: 1920x1080px'),

                        TextInput::make('trailer_url')
                            ->url(),

                        Select::make('video_upload_type_id')
                            ->label('Video Upload Type')
                            ->relationship('videoUploadType', 'name')
                            ->options(VideoUploadType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
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
                    ])->columns(3),

                Section::make('Details')
                    ->schema([
                        TextInput::make('episodes_count')
                            ->label('Total Episodes (Planned)')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Total number of episodes planned for this anime'),

                        TextInput::make('duration')
                            ->numeric()
                            ->suffix('minutes'),

                        DatePicker::make('release_date'),

                        TextInput::make('rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step(0.1),

                        // PERMISSION FIX: Only ADMIN can create new studios
                        Select::make('studio_id')
                            ->relationship('studio', 'name')
                            ->searchable()
                            ->createOptionForm(auth()->user()->can('create_studio') ? [
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->required(),
                                TextInput::make('website')
                                    ->url(),
                                TextInput::make('founded_year')
                                    ->numeric(),
                                Toggle::make('is_active')
                                    ->default(true),
                            ] : null)
                            ->label('Studio')
                            ->preload()
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new studios' : null),

                        TextInput::make('source')
                            ->placeholder('e.g., Manga, Light Novel, Original'),
                    ])->columns(3),

                Section::make('Categories & Genres')
                    ->schema([
                        // PERMISSION FIX: Only ADMIN can create new genres
                        Select::make('genres')
                            ->relationship('genres', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(auth()->user()->can('create_genre') ? [
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->required(),
                                ColorPicker::make('color'),
                            ] : null)
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new genres' : null),

                        // PERMISSION FIX: Only ADMIN can create new categories
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(auth()->user()->can('create_category') ? [
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->required(),
                                TextInput::make('icon')
                                    ->placeholder('heroicon name'),
                            ] : null)
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new categories' : null),
                    ])->columns(2),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('Featured Anime')
                            ->visible(fn() => auth()->user()->can('publish_anime')),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->visible(fn() => auth()->user()->can('publish_anime'))
                            ->helperText(fn() => auth()->user()->hasRole('EDITOR') ? 'Only admins can publish content' : null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Cache user permissions to avoid repeated checks
        $canPublish = auth()->user()->can('publish_anime');
        $canDelete = auth()->user()->can('delete_anime');

        return $table
            ->columns([
                ImageColumn::make('poster_image')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->defaultImageUrl(function () {
                        return asset('images/no-images.png');
                    })
                    ->extraImgAttributes(['alt' => 'Poster'])
                    ->checkFileExistence(false),

                ImageColumn::make('cover_image')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->label('Cover')
                    ->defaultImageUrl(function () {
                        return asset('images/no-images.png');
                    })
                    ->extraImgAttributes(['alt' => 'Cover'])
                    ->checkFileExistence(false),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->extraAttributes([
                        'style' => 'min-width: 350px !important; width: 350px !important; white-space: normal !important; word-wrap: break-word !important;',
                        'class' => 'title-column'
                    ])
                    ->weight('medium'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'upcoming',
                        'success' => 'ongoing',
                        'primary' => 'completed',
                        'danger' => 'hiatus',
                    ]),

                TextColumn::make('type')
                    ->badge()
                    ->size('sm'),

                TextColumn::make('episodes_count')
                    ->label('Planned')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->size('sm'),

                TextColumn::make('actual_episodes_count')
                    ->label('Uploaded')
                    ->sortable()
                    ->badge()
                    ->size('sm')
                    ->color(fn($record) => $record->actual_episodes_count >= $record->episodes_count ? 'success' : 'warning'),

                TextColumn::make('studio.name')
                    ->label('Studio')
                    ->sortable()
                    ->limit(15)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 15 ? $state : null;
                    }),

                TextColumn::make('videoUploadType.name')
                    ->label('Upload Type')
                    ->badge()
                    ->color('info')
                    ->size('sm')
                    ->placeholder('Not set'),

                TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->visible($canPublish),

                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->visible($canPublish),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'hiatus' => 'Hiatus',
                    ]),

                SelectFilter::make('type')
                    ->options([
                        'tv' => 'TV Series',
                        'movie' => 'Movie',
                        'ova' => 'OVA',
                        'ona' => 'ONA',
                        'special' => 'Special',
                    ]),

                SelectFilter::make('studio')
                    ->relationship('studio', 'name')
                    ->searchable(),

                SelectFilter::make('video_upload_type')
                    ->relationship('videoUploadType', 'name')
                    ->searchable()
                    ->label('Upload Type'),

                TernaryFilter::make('is_featured'),
                TernaryFilter::make('is_published'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible($canDelete),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible($canDelete),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->extremePaginationLinks();
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
            'index' => Pages\ListAnimes::route('/'),
            'create' => Pages\CreateAnime::route('/create'),
            'edit' => Pages\EditAnime::route('/{record}/edit'),
        ];
    }
}
