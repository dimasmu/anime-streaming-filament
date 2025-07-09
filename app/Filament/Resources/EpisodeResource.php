<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpisodeResource\Pages;
use App\Models\Episode;
use App\Models\Anime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EpisodeResource extends Resource
{
    protected static ?string $model = Episode::class;

    protected static ?string $navigationIcon = 'heroicon-o-play';

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Episode Information')
                    ->schema([
                        Forms\Components\Select::make('anime_id')
                            ->relationship('anime', 'title')
                            ->searchable()
                            ->preload()
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
                        
                        Forms\Components\TextInput::make('duration')
                            ->numeric()
                            ->suffix('minutes'),
                    ])->columns(3),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
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
                
                Tables\Columns\TextColumn::make('duration')
                    ->suffix(' min')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('air_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->searchable()
                    ->preload(),
                
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