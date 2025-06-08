<?php

namespace App\Filament\Guide\Resources;

use App\Filament\Guide\Resources\GuideTripResource\Pages;
use App\Filament\Guide\Resources\GuideTripResource\RelationManagers;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Concerns\Translatable;
use App\Models\GuideTrip;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuideTripResource extends Resource
{
    protected static ?string $model = GuideTrip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Guide Trips';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('guide_id', auth()->id())->count();
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //trip name and description
                Section::make('General information')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Trip Name')
                                    ->required()
                                    ->translatable(),
                            ])
                            ->columnSpanFull(),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Trip Description')
                                    ->required()
                                    ->translatable(),

                            ])
                            ->columnSpanFull(),
                    ]),
                // 🔹 Trip Schedule
                Section::make('Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_datetime')
                                    ->required()
                                    ->minDate(now())
                                    ->reactive() // Make it reactive so changes can trigger updates
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Ensure end_datetime is not before start_datetime
                                        $set('end_datetime', null);
                                    }),
                                Forms\Components\DateTimePicker::make('end_datetime')
                                    ->required()
                                    ->minDate(fn (callable $get) => $get('start_datetime')) // Ensure it starts after start_datetime
                                    ->rules(['after:start_datetime']),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🔹 Pricing & Attendance
                Section::make('Pricing & Capacity')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('main_price')
                                    ->label('Main Price')
                                    ->required()
                                    ->numeric()
                                     ->minValue(0),

                                Forms\Components\TextInput::make('max_attendance')
                                    ->required()
                                    ->label('Max Attendees')
                                    ->numeric()
                                    ->minValue(1),

                      ]),
                    ])
                    ->columnSpanFull(),

                // 🔹 Status Selection
                Section::make('Trip Status')
                    ->schema([
                        Forms\Components\Toggle::make('status')->required()->inline(false),
                    ])
                    ->columnSpanFull(),

                // 🔹 Trip Activities & Assemblies (Side by Side)
                Section::make('Trip Details')
                    ->schema([
                        Grid::make(1) // 🔹 Ensures two columns layout for Repeaters
                        ->schema([
                            // 🔹 Trip Activities
                            Repeater::make('activities')
                                ->label('Activities')
                                ->relationship('activities')
                                ->schema([
                                    Forms\Components\TextInput::make('activity')
                                        ->label('Activity')
                                        ->translatable(),
                                ])
                                ->columns(1)
                                ->columnSpan(1)
                                ->required(),
//                                // Ensures it stays in one column

                            // 🔹 Trip Assemblies
                            Repeater::make('assemblies')
                                ->label('Assemblies')
                                ->relationship('assemblies')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('place')
                                                ->label('Place')
                                                ->required()
                                                ->translatable(),


                                            Forms\Components\TimePicker::make('time')
                                                ->label('Time')
                                                ->required(),
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
                                            Forms\Components\TextInput::make('min_age')
                                                ->label('Min Age')
                                                ->numeric()
                                                ->minValue(0),

                                            Forms\Components\TextInput::make('max_age')
                                                ->label('Max Age')
                                                ->numeric()
                                                ->minValue(0)
                                                ->rule(function (\Filament\Forms\Get $get) {
                                                    $minAge = $get('min_age');
                                                    return fn ($state): ?string =>
                                                    $state <= $minAge ? 'Max age must be greater than min age.' : null;
                                                }),

                                            Forms\Components\TextInput::make('price')
                                                ->label('Price')
                                                ->numeric()
                                                ->minValue(0)
                                                ->step(0.1),
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
                                            Forms\Components\TextInput::make('include')
                                                ->label('Include')
                                                ->required()
                                                ->translatable(),
                                        ]),
                                ])
                                ->columns(1)
                                ->columnSpan(1), // Ensures it stays in one column
                            // 🔹 Trip Requirements
                            Repeater::make('requirements')
                                ->label('Requirements')
                                ->relationship('requirements')
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Forms\Components\TextInput::make('item')
                                                ->label('item')
                                                ->translatable(),
                                        ]),
                                ])
                                ->columns(1)
                                ->columnSpan(1), // Ensures it stays in one column

                            Repeater::make('payment_method')
                                ->label('Pyment Method')
                                ->relationship('paymentMethods')
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Forms\Components\TextInput::make('method')
                                                ->label('item')
                                                ->translatable(),
                                        ]),
                                ])
                                ->columns(1)
                                ->columnSpan(1), // Ensures it stays in one column
                            // 🔹 Trip Trail

                            Forms\Components\Toggle::make('is_trail')
                                ->label('Has Trail?')
                                ->live()
                                ->afterStateHydrated(function (Forms\Components\Toggle $component, $state) {
                                    $record = $component->getRecord();

                                    if ($record && $record->trail) {
                                        $component->state(true);
                                    } else {
                                        $component->state(false);
                                    }
                                }),

                            Section::make('Trail Details')
                                ->schema([
                                    Grid::make(4)->schema([
                                        Forms\Components\TextInput::make('min_duration_in_minute')
                                            ->label('Min Duration')
                                            ->numeric()
                                            ->nullable()
                                            ->required(fn (\Filament\Forms\Get $get) => $get('is_trail'))
                                            ->afterStateHydrated(function ($component, $state) {
                                                $trail = $component->getRecord()?->trail;

                                                if ($trail) {
                                                    $component->state($trail->min_duration_in_minute);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('max_duration_in_minute')
                                            ->label('Max Duration')
                                            ->numeric()
                                            ->nullable()
                                            ->required(fn (\Filament\Forms\Get $get) => $get('is_trail'))
                                            ->afterStateHydrated(function ($component, $state) {
                                                $trail = $component->getRecord()?->trail;

                                                if ($trail) {
                                                    $component->state($trail->max_duration_in_minute);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('distance_in_meter')
                                            ->label('Distance')
                                            ->numeric()
                                            ->nullable()
                                            ->required(fn (\Filament\Forms\Get $get) => $get('is_trail'))
                                            ->afterStateHydrated(function ($component, $state) {
                                                $trail = $component->getRecord()?->trail;

                                                if ($trail) {
                                                    $component->state($trail->distance_in_meter);
                                                }
                                            }),

                                        Forms\Components\Select::make('difficulty')
                                            ->label('Difficulty')
                                            ->options([
                                                0 => 'Easy',
                                                1 => 'Moderate',
                                                2 => 'Hard',
                                                3 => 'Very Hard',
                                            ])
                                            ->nullable()
                                            ->required(fn (\Filament\Forms\Get $get) => $get('is_trail'))
                                            ->afterStateHydrated(function ($component, $state) {
                                                $trail = $component->getRecord()?->trail;

                                                if ($trail) {
                                                    $component->state($trail->difficulty);
                                                }
                                            }),
                                    ]),
                                ])
                                ->visible(fn (\Filament\Forms\Get $get) => $get('is_trail'))


                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Media Uploads')
                    ->description('Upload main and gallery images.')
                    ->schema([
                        Grid::make(1)->schema([
                            SpatieMediaLibraryFileUpload::make('main_image')
                                ->label('Main Image')
                                ->collection('main_image')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('guide_trip_gallery')
                                ->label('Gallery Images')
                                ->collection('guide_trip_gallery')
                                ->multiple()
                                ->required()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ]),
                    ])
                    ->collapsible()
                    ->columns(1),
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
                Tables\Actions\EditAction::make(),
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
//            'view' => Pages\ViewGuideTrip::route('/{record}'),
            'edit' => Pages\EditGuideTrip::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('guide_id', auth()->id());
    }
}
