<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookmarkResource\Pages;
use App\Models\Bookmark;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookmarkResource extends Resource
{
    protected static ?string $model = Bookmark::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Viewer Management';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_bookmark');
    }

    public static function canCreate(): bool
    {
        return false; // Bookmarks are created via API only
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_bookmark');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_bookmark');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'anime', 'episode'])
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bookmark Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('anime_id')
                            ->relationship('anime', 'title')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('episode_id')
                            ->relationship('episode', 'title')
                            ->searchable()
                            ->disabled(),
                    ])->columns(3),
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('timestamp')
                            ->numeric()
                            ->suffix('seconds')
                            ->helperText('Position in the video'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anime.title')
                    ->label('Anime')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('episode.title')
                    ->label('Episode')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->searchable(),
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
            'index' => Pages\ListBookmarks::route('/'),
            'edit' => Pages\EditBookmark::route('/{record}/edit'),
        ];
    }
}
