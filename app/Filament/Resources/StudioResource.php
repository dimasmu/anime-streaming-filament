<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudioResource\Pages;
use App\Models\Studio;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, TextInput, Textarea, FileUpload, Toggle};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn};
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\{EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction};
use Illuminate\Support\Str;

class StudioResource extends Resource
{
    protected static ?string $model = Studio::class;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('ADMIN');
    }

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Studio Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                        
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        
                        TextInput::make('website')
                            ->url()
                            ->placeholder('https://studio-website.com'),
                        
                        TextInput::make('founded_year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y'))
                            ->placeholder('e.g., 1998'),
                    ])->columns(2),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->directory('studios/logos'),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Studio')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->size(50)
                    ->square(),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                TextColumn::make('founded_year')
                    ->label('Founded')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('website')
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->limit(25)
                    ->color('blue'),
                
                TextColumn::make('animes_count')
                    ->counts('animes')
                    ->label('Animes')
                    ->badge()
                    ->color('primary'),
                
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Studios'),
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
            ->striped()
            ->paginated([10, 25, 50]);
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
            'index' => Pages\ListStudios::route('/'),
            'create' => Pages\CreateStudio::route('/create'),
            'edit' => Pages\EditStudio::route('/{record}/edit'),
        ];
    }
}