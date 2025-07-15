<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Password;
use Filament\Forms\Components\Select;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Users Management';
    
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true; // Always show in navigation, but access is controlled by canViewAny
    }

    public static function canViewAny(): bool
    {
        // Allow access if user has admin role or specific user permissions
        return Auth::user()->hasRole('ADMIN') || Auth::user()->can('view_user');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasRole('ADMIN') || Auth::user()->can('create_user');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->hasRole('ADMIN') || Auth::user()->can('edit_user');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->hasRole('ADMIN') || Auth::user()->can('delete_user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->description('Enter the user details below')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter full name')
                            ->autocomplete('name'),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter email address')
                            ->autocomplete('email'),

                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $context) => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->placeholder(fn (string $context) => $context === 'create' ? 'Enter password' : 'Leave blank to keep current password')
                            ->helperText(fn (string $context) => $context === 'edit' ? 'Leave blank to keep the current password' : 'Minimum 8 characters')
                            ->autocomplete('new-password'),

                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required()
                            ->placeholder('Select user roles')
                            ->helperText('Select one or more roles for this user'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('View User Details')
                    ->modalWidth('lg'),

                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit User')
                    ->modalButton('Save Changes')
                    ->modalWidth('lg')
                    ->successNotificationTitle('User updated successfully'),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete User')
                    ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it')
                    ->successNotificationTitle('User deleted successfully'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('Are you sure you want to delete the selected users? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->successNotificationTitle('Users deleted successfully'),
                ]),
            ])
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Get started by creating your first user.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Create New User')
                    ->modalButton('Create User')
                    ->modalWidth('lg')
                    ->successNotificationTitle('User created successfully'),
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
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
