<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Facades\Filament;

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
            ->with(['studio', 'genres', 'categories']) // Eager load relationships
            ->withCount('episodes'); // Add episode count efficiently
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
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, callable $set, callable $get) {
                                if ($context === 'create' || empty($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from title'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'hiatus' => 'Hiatus',
                            ])
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'tv' => 'TV Series',
                                'movie' => 'Movie',
                                'ova' => 'OVA',
                                'ona' => 'ONA',
                                'special' => 'Special',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\RichEditor::make('synopsis')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('poster_image')
                            ->image()
                            ->disk('public')
                            ->directory('anime/posters')
                            ->visibility('public'),

                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('anime/covers')
                            ->visibility('public'),

                        Forms\Components\TextInput::make('trailer_url')
                            ->url(),
                    ])->columns(3),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\TextInput::make('episodes_count')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\TextInput::make('duration')
                            ->numeric()
                            ->suffix('minutes'),

                        Forms\Components\DatePicker::make('release_date'),

                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step(0.1),

                        // PERMISSION FIX: Only ADMIN can create new studios
                        Forms\Components\Select::make('studio_id')
                            ->relationship('studio', 'name')
                            ->searchable()
                            ->createOptionForm(auth()->user()->can('create_studio') ? [
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                                Forms\Components\TextInput::make('website')
                                    ->url(),
                                Forms\Components\TextInput::make('founded_year')
                                    ->numeric(),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ] : null)
                            ->label('Studio')
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new studios' : null),

                        Forms\Components\TextInput::make('source')
                            ->placeholder('e.g., Manga, Light Novel, Original'),
                    ])->columns(3),

                Forms\Components\Section::make('Categories & Genres')
                    ->schema([
                        // PERMISSION FIX: Only ADMIN can create new genres
                        Forms\Components\Select::make('genres')
                            ->relationship('genres', 'name')
                            ->multiple()
                            ->searchable()
                            ->createOptionForm(auth()->user()->can('create_genre') ? [
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                                Forms\Components\ColorPicker::make('color'),
                            ] : null)
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new genres' : null),

                        // PERMISSION FIX: Only ADMIN can create new categories
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->createOptionForm(auth()->user()->can('create_category') ? [
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                                Forms\Components\TextInput::make('icon')
                                    ->placeholder('heroicon name'),
                            ] : null)
                            ->helperText(auth()->user()->hasRole('EDITOR') ? 'Contact admin to add new categories' : null),
                    ])->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Anime')
                            ->visible(fn () => auth()->user()->can('publish_anime')),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->visible(fn () => auth()->user()->can('publish_anime'))
                            ->helperText(fn () => auth()->user()->hasRole('EDITOR') ? 'Only admins can publish content' : null),
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
                Tables\Columns\ImageColumn::make('poster_image')
                    ->disk('public')
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30), // Add limit for better performance

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'upcoming',
                        'success' => 'ongoing',
                        'primary' => 'completed',
                        'danger' => 'hiatus',
                    ]),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('episodes_count')
                    ->label('Episodes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('studio.name')
                    ->label('Studio')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->sortable(),

                // PERFORMANCE FIX: Cache permission check
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->visible($canPublish),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Published')
                    ->visible($canPublish),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'hiatus' => 'Hiatus',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'tv' => 'TV Series',
                        'movie' => 'Movie',
                        'ova' => 'OVA',
                        'ona' => 'ONA',
                        'special' => 'Special',
                    ]),

                // PERFORMANCE FIX: Remove preload() from filters
                Tables\Filters\SelectFilter::make('studio')
                    ->relationship('studio', 'name')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible($canDelete), // Use cached permission
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($canDelete), // Use cached permission
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
            'index' => Pages\ListAnimes::route('/'),
            'create' => Pages\CreateAnime::route('/create'),
            'edit' => Pages\EditAnime::route('/{record}/edit'),
        ];
    }
}
