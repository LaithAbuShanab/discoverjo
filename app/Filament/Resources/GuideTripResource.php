<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideTripResource\Pages;
use App\Models\GuideTrip;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, Grid, Repeater, Placeholder};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuideTripResource extends Resource
{
    protected static ?string $model = GuideTrip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Guide Trips';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 🔹 Guide Selection (Dropdown)
                Section::make('Guide Details')
                    ->schema([
                        Placeholder::make('guide')
                            ->label('Guide')
                            ->content(fn($record) => $record->guide->username ?? 'N/A'),
                    ])
                    ->columnSpanFull(),

                // 🔹 Trip Information
                Section::make('Trip Details')
                    ->schema([
                        Grid::make(2) // Two-column layout
                            ->schema([
                                Placeholder::make('name')
                                    ->label('Trip Name')
                                    ->content(fn($record) => $record->name ?? 'N/A'),

                                Placeholder::make('description')
                                    ->label('Description')
                                    ->content(fn($record) => $record->description ?? 'N/A'),
                            ]),

                        Placeholder::make('slug')
                            ->label('Slug')
                            ->content(fn($record) => $record->slug ?? 'N/A')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // 🔹 Trip Schedule
                Section::make('Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('start_datetime')
                                    ->label('Start Date & Time')
                                    ->content(fn($record) => $record->start_datetime ?? 'N/A'),

                                Placeholder::make('end_datetime')
                                    ->label('End Date & Time')
                                    ->content(fn($record) => $record->end_datetime ?? 'N/A'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🔹 Pricing & Attendance
                Section::make('Pricing & Capacity')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('main_price')
                                    ->label('Main Price')
                                    ->content(fn($record) => '$' . ($record->main_price ?? 'N/A')),

                                Placeholder::make('max_attendance')
                                    ->label('Max Attendees')
                                    ->content(fn($record) => $record->max_attendance ?? 'N/A'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🔹 Status Selection
                Section::make('Trip Status')
                    ->schema([
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn($record) => $record->status === 1 ? 'Active' : 'Inactive'),
                    ])
                    ->columnSpanFull(),

                // 🔹 Trip Activities & Assemblies (Side by Side)
                Section::make('Trip Details')
                    ->schema([
                        Grid::make(2) // 🔹 Ensures two columns layout for Repeaters
                            ->schema([
                                // 🔹 Trip Activities
                                Repeater::make('activities')
                                    ->label('Activities')
                                    ->relationship('activities')
                                    ->schema([
                                        Placeholder::make('activity')
                                            ->label('Activity')
                                            ->content(fn($record) => $record->activity ?? 'N/A'),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column

                                // 🔹 Trip Assemblies
                                Repeater::make('assemblies')
                                    ->label('Assemblies')
                                    ->relationship('assemblies')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Placeholder::make('place')
                                                    ->label('Place')
                                                    ->content(fn($record) => $record->place ?? 'N/A'),

                                                Placeholder::make('time')
                                                    ->label('Time')
                                                    ->content(fn($record) => $record->time ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column
                                // 🔹 Trip Price & Age
                                Repeater::make('priceAges')
                                    ->label('Price Ages')
                                    ->relationship('priceAges')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('min_age')
                                                    ->label('Min Age')
                                                    ->content(fn($record) => $record->min_age ?? 'N/A'),

                                                Placeholder::make('max_age')
                                                    ->label('Max Age')
                                                    ->content(fn($record) => $record->max_age ?? 'N/A'),

                                                Placeholder::make('price')
                                                    ->label('Price')
                                                    ->content(fn($record) => $record->price ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column
                                // 🔹 Trip Price Included
                                Repeater::make('priceIncludes')
                                    ->label('Price Includes')
                                    ->relationship('priceIncludes')
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                Placeholder::make('include')
                                                    ->label('Include')
                                                    ->content(fn($record) => $record->include ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column
                                // 🔹 Trip Requirements
                                Repeater::make('requirements')
                                    ->label('Requirements')
                                    ->relationship('requirements')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('item')
                                                    ->label('item')
                                                    ->content(fn($record) => $record->item ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column
                                // 🔹 Trip Trail
                                Repeater::make('trail')
                                    ->label('Trail')
                                    ->relationship('trail')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Placeholder::make('min_duration_in_minute')
                                                    ->label('Min Duration')
                                                    ->content(fn($record) => $record->min_duration_in_minute ?? 'N/A'),

                                                Placeholder::make('max_duration_in_minute')
                                                    ->label('Max Duration')
                                                    ->content(fn($record) => $record->max_duration_in_minute ?? 'N/A'),

                                                Placeholder::make('distance_in_meter')
                                                    ->label('Distance')
                                                    ->content(fn($record) => $record->distance_in_meter ?? 'N/A'),

                                                Placeholder::make('difficulty')
                                                    ->label('Difficulty')
                                                    ->content(fn($record) => $record->difficulty ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column

                            ]),
                    ])
                    ->columnSpanFull(),
                // 🔹 Trip Users
                Section::make('Trip Users')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                // 🔹 Trip Users
                                Repeater::make('guideTripUsers')
                                    ->label('Users')
                                    ->relationship('guideTripUsers')
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                Placeholder::make('user_id')
                                                    ->label('Username')
                                                    ->content(fn($record) => $record->user->username ?? 'N/A'),

                                                Placeholder::make('first_name')
                                                    ->label('First Name')
                                                    ->content(fn($record) => $record->first_name ?? 'N/A'),

                                                Placeholder::make('last_name')
                                                    ->label('Last Name')
                                                    ->content(fn($record) => $record->last_name ?? 'N/A'),

                                                Placeholder::make('phone_number')
                                                    ->label('Phone Number')
                                                    ->content(fn($record) => $record->phone_number ?? 'N/A'),

                                                Placeholder::make('age')
                                                    ->label('Age')
                                                    ->content(fn($record) => $record->age ?? 'N/A'),

                                                Placeholder::make('status')
                                                    ->label('Status')
                                                    ->content(fn($record) => $record->status === 1 ? 'Active' : 'Inactive' ?? 'N/A'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->columnSpan(1), // Ensures it stays in one column
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guide.username')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_datetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('main_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_attendance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('status')
                    ->sortable(),
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
            'index' => Pages\ListGuideTrips::route('/'),
            'create' => Pages\CreateGuideTrip::route('/create'),
            'view' => Pages\ViewGuideTrip::route('/{record}'),
            'edit' => Pages\EditGuideTrip::route('/{record}/edit'),
        ];
    }
}
