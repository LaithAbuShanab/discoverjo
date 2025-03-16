<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Filament\Resources\TripResource\RelationManagers;
use App\Models\Trip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Support\Str;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class TripResource extends Resource
{

    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Trips';

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
                Section::make('Basic Information')
                    ->description('Provide the basic details of the trip.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'username')
                                    ->required()
                                    ->label('User'),

                                Forms\Components\Select::make('place_id')
                                    ->relationship('place', 'name')
                                    ->required()
                                    ->label('Place')
                                    ->preload(true)
                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Trip Name')
                                    ->reactive(),

                                Forms\Components\TextInput::make('slug')
                                    ->maxLength(255)
                                    ->label('Slug')
                                    ->disabled(),
                            ]),

                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->columnSpanFull()
                                    ->label('Description'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Trip Details')
                    ->description('Set the trip type, cost, and other specifications.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('trip_type')
                                    ->required()
                                    ->options([
                                        0 => 'Public',
                                        1 => 'Followers',
                                        2 => 'Specific Users',
                                    ])
                                    ->label('Trip Type'),

                                Forms\Components\TextInput::make('cost')
                                    ->required()
                                    ->numeric()
                                    ->prefix('JOD')
                                    ->label('Cost'),
                            ]),

                        Forms\Components\Fieldset::make('Age Range')
                            ->schema([
                                Forms\Components\TextInput::make('min_age')
                                    ->label('Minimum Age')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                Forms\Components\TextInput::make('max_age')
                                    ->label('Maximum Age')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(2)

                            ->afterStateHydrated(function ($state, Forms\Set $set, $record) {
                                if ($record && is_string($record->age_range)) {
                                    $ageRange = json_decode($record->age_range, true);
                                    if (is_array($ageRange)) {
                                        $set('min_age', $ageRange['min'] ?? null);
                                        $set('max_age', $ageRange['max'] ?? null);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('sex')
                            ->required()
                            ->options([
                                0 => 'Both',
                                1 => 'Male',
                                2 => 'Female',
                            ])
                            ->label('Sex'),
                    ])
                    ->columns(1),

                Section::make('Event Timing and Attendance')
                    ->description('Specify the event date, time, and attendance details.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_time')
                                    ->required()
                                    ->label('Date and Time'),

                                Forms\Components\TextInput::make('attendance_number')
                                    ->numeric()
                                    ->label('Attendance Number'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Status')
                    ->description('Set the trip status.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        0 => 'Inactive',
                                        1 => 'Active',
                                        2 => 'Deleted by Creator',
                                        3 => 'Deleted by Admin',
                                    ])
                                    ->default(1)
                                    ->label('Status'),
                            ]),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.username'),
                Tables\Columns\TextColumn::make('place.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trip_type')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            0 => 'Public',
                            1 => 'Followers',
                            2 => 'Specific Users',
                            default => 'Unknown',
                        };
                    })
                    ->label('Trip Type')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cost')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sex')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance_number')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Admin Deletion')
                    ->afterStateUpdated(function ($record, $state) {
                        $record->status = $state ? 3 : 1;
                        $record->save();
                    })
                    ->state(fn($record) => $record->status === 3), // Toggle is "on" when status is 3

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
            RelationManagers\UsersTripRelationManager::class,
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'view' => Pages\ViewTrip::route('/{record}/view'),
        ];
    }
}
