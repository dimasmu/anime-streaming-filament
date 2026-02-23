<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Viewer Management';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_comment');
    }

    public static function canCreate(): bool
    {
        return false; // Comments are created via API only
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_comment');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_comment');
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
                Forms\Components\Section::make('Comment Information')
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
                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_spoiler')
                            ->label('Contains Spoilers'),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible to Users'),
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
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_spoiler')
                    ->label('Spoiler')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_spoiler')
                    ->label('Has Spoilers')
                    ->placeholder('All comments')
                    ->trueLabel('With spoilers only')
                    ->falseLabel('Without spoilers'),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->placeholder('All comments')
                    ->trueLabel('Visible only')
                    ->falseLabel('Hidden only'),
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
            'index' => Pages\ListComments::route('/'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
