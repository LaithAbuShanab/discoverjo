<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Filament\Forms\Components\{Section, Grid};

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'App users';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Info Section
                Section::make('Basic Information')
                    ->description('General user identity details.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->maxLength(255)
                                    ->label('First Name'),

                                Forms\Components\TextInput::make('last_name')
                                    ->maxLength(255)
                                    ->label('Last Name'),

                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Username'),

                                Forms\Components\TextInput::make('slug')
                                    ->maxLength(255)
                                    ->label('Slug'),

                                Forms\Components\DatePicker::make('birthday')
                                    ->label('Birthday'),

                                Forms\Components\Select::make('sex')
                                    ->options([
                                        '1' => 'Male',
                                        '2' => 'Female',
                                    ])
                                    ->label('Sex'),
                            ]),
                    ])
                    ->columns(1),

                // Authentication Section
                Section::make('Authentication')
                    ->description('Email, password and social login.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Email'),

                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At'),

                                Forms\Components\TextInput::make('facebook_id')
                                    ->maxLength(255)
                                    ->label('Facebook ID'),

                                Forms\Components\TextInput::make('google_id')
                                    ->maxLength(255)
                                    ->label('Google ID'),
                            ]),
                    ])
                    ->columns(1),

                // Contact Info
                Section::make('Contact & Language')
                    ->description('Phone and language preferences.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone_number')
                                    ->tel()
                                    ->maxLength(255)
                                    ->label('Phone Number'),

                                Forms\Components\TextInput::make('lang')
                                    ->required()
                                    ->default('ar')
                                    ->maxLength(255)
                                    ->label('Language'),
                            ]),
                    ])
                    ->columns(1),

                // Location & Points
                Section::make('Location & Points')
                    ->description('Geolocation and gamification data.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('address')
                                    ->translatable()
                                    ->label('Address'),

                                Forms\Components\TextInput::make('points')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->label('Points'),

                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->label('Longitude'),

                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->label('Latitude'),
                            ]),
                    ])
                    ->columns(1),

                // Status Section
                Section::make('User Status')
                    ->description('Activation and role of the user.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->required()
                                    ->default(2)
                                    ->label('Status'),

                                Forms\Components\Toggle::make('is_guide')
                                    ->required()
                                    ->label('Is Guide?'),
                            ]),
                    ])
                    ->columns(1),

                // Description (full width)
                Section::make('Profile Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->label('Description'),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('birthday')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sex')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state === 1 ? 'male' : ($state === 2 ? 'female' : null)),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                ActivityLogTimelineTableAction::make('Activities'),
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
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
