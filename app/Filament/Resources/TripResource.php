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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // User Relationship
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'username')
                    ->required()
                    ->label('User'),

                // Place Relationship
                Forms\Components\Select::make('place_id')
                    ->relationship('place', 'name')
                    ->required()
                    ->label('Place'),

                // Trip Type as a Select
                Forms\Components\Select::make('trip_type')
                    ->required()
                    ->options([
                        0 => 'Public',
                        1 => 'Followers',
                        2 => 'Specific Users',
                    ])
                    ->label('Trip Type'),

                // Trip Name
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Trip Name'),

                // Slug
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->label('Slug'),

                // Description
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('Description'),

                // Cost with Numeric Validation
                Forms\Components\TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->prefix('JOD')
                    ->label('Cost'),

                Forms\Components\Fieldset::make('Age Range')
                    ->schema([
                        Forms\Components\TextInput::make('age_range.min')
                            ->label('Minimum Age')
                            ->required()
                            ->numeric()
                            ->minValue(0), // Ensure the value is non-negative

                        Forms\Components\TextInput::make('age_range.max')
                            ->label('Maximum Age')
                            ->required()
                            ->numeric()
                            ->minValue(1), // Ensure the value is greater than zero
                    ])
                    ->columns(2)
                    ->label('Age Range'),


                // Sex (using Select for predefined options, e.g., Male/Female/Other)
                Forms\Components\Select::make('sex')
                    ->required()
                    ->options([
                        1 => 'Male',
                        2 => 'Female',
                    ])
                    ->label('Sex'),

                // Date and Time Picker
                Forms\Components\DateTimePicker::make('date_time')
                    ->required()
                    ->label('Date and Time'),

                // Attendance Number
                Forms\Components\TextInput::make('attendance_number')
                    ->numeric()
                    ->label('Attendance Number'),

                // Status as a Select
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
            ]);
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
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            0 => 'Inactive',
                            1 => 'Active',
                            2 => 'Deleted by Creator',
                            3 => 'Deleted by Admin',
                            default => 'Unknown',
                        };
                    })
                    ->label('Status')
                    ->sortable()
                    ->searchable(),

//                Tables\Columns\ToggleColumn::make('status')
//                    ->label('Admin Deletion')
//                    ->action(function ($record, $state) {
//                        if ($record->status != 3 && $state) {
//                            $record->update(['status' => 3]); // Change to Deleted by Admin
//                        } elseif ($record->status == 3 && !$state) {
//                            $record->update(['status' => 1]); // Change back to Active
//                        }
//                    })
//                    ->onColor('warning') // Set color for the "on" state
//                    ->offColor('secondary') // Set color for the "off" state
//                    ->state(function ($record) {
//                        return $record->status == 3; // Toggle is "on" when status is 3
//                    }),
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
                //process of delete should change the status
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'view'=>Pages\ViewTrip::route('/{record}/view'),
//            'create' => Pages\CreateTrip::route('/create'),
//            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
