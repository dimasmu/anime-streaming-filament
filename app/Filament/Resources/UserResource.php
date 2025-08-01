<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, TextInput, Select};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, BadgeColumn, TextInputColumn, ToggleColumn};
use Filament\Tables\Actions\{EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction};
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
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
        // All users can create users
        return true;
    }

    public static function canEdit($record): bool
    {
        // ADMIN users can edit non-ADMIN users only
        if (Auth::user()->hasRole('ADMIN')) {
            return !$record->hasRole('ADMIN');
        }
        // Other users can edit any user
        return true;
    }

    public static function canDelete($record): bool
    {
        // ADMIN users can delete non-ADMIN users only
        if (Auth::user()->hasRole('ADMIN')) {
            return !$record->hasRole('ADMIN');
        }
        // Other users can delete any user
        return true;
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
                            ->required(fn(string $context) => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->placeholder(fn(string $context) => $context === 'create' ? 'Enter password' : 'Leave blank to keep current password')
                            ->helperText(fn(string $context) => $context === 'edit' ? 'Leave blank to keep the current password' : 'Minimum 8 characters')
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
            ->recordClasses(fn($record) => match (true) {
                $record->hasRole('ADMIN') => 'bg-red-50 border-l-4 border-red-500', // Red background for restricted ADMIN
                $record->hasRole('EDITOR') => 'bg-green-50 border-l-4 border-green-500', // Green background for EDITOR with permissions
                $record->hasRole('USER_MANAGER') => 'bg-blue-50 border-l-4 border-blue-500', // Blue background for USER_MANAGER
                default => 'bg-gray-50 border-l-4 border-gray-300' // Gray for others
            })
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
                    ->color(fn($record) => match (true) {
                        $record->hasRole('ADMIN') => 'danger', // Red for ADMIN (restricted from user management)
                        $record->hasRole('EDITOR') => 'warning', // Orange for EDITOR (limited permissions)
                        $record->hasRole('USER_MANAGER') => 'success', // Green for USER_MANAGER (can manage users)
                        default => 'gray'
                    })
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
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading('View User Details')
                    ->modalWidth('lg'),

                EditAction::make()
                    ->modalHeading('Edit User')
                    ->modalButton('Save Changes')
                    ->modalWidth('lg')
                    ->successNotificationTitle('User updated successfully')
                    ->disabled(fn($record) => Auth::user()->hasRole('ADMIN') && $record->hasRole('ADMIN'))
                    ->tooltip(fn($record) => Auth::user()->hasRole('ADMIN') && $record->hasRole('ADMIN') ? 'ADMIN users cannot edit other ADMIN users' : null),

                DeleteAction::make()
                    ->modalHeading('Delete User')
                    ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it')
                    ->successNotificationTitle('User deleted successfully')
                    ->disabled(fn($record) => Auth::user()->hasRole('ADMIN') && $record->hasRole('ADMIN'))
                    ->tooltip(fn($record) => Auth::user()->hasRole('ADMIN') && $record->hasRole('ADMIN') ? 'ADMIN users cannot delete other ADMIN users' : null),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('Are you sure you want to delete the selected users? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->successNotificationTitle('Users deleted successfully'),
                ]),
            ])
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Get started by creating your first user.')
            ->emptyStateActions([
                CreateAction::make()
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
