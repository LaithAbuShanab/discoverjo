<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Place Details Section
                Section::make('Service Details')
                    ->description('Main information about the Service.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')->label('Name')->required()->placeholder('Please Enter Name')->translatable(),
                            TextInput::make('address')->label('Address')->required()->placeholder('Please Enter Address')->translatable(),
                            Textarea::make('description')->label('Description')->rows(5)->required()->placeholder('Please Enter Description')->translatable()->columnSpan(2),
                            TextInput::make('url_google_map')->label('Google Map URL')->required()->placeholder('Enter Google Map URL'),
                            Select::make('categories')
                                ->label('Categories')
                                ->relationship('categories', 'name', fn($query) => $query->whereNotNull('parent_id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),
                            Select::make('region_id')->label('Region')->relationship('region', 'name')->required(),
                            Forms\Components\TextInput::make('price')->placeholder('Please Enter Price')->nullable()->numeric(),
                            Toggle::make('status')->label('Status')->required(),
                        ])
                    ])
                    ->collapsible()
                    ->columns(1),

                Repeater::make('serviceBookings')
                    ->label('Available Services')
                    ->relationship('serviceBookings')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('available_start_date')
                                ->label('Available Start Date')
                                ->required(),

                            Forms\Components\DatePicker::make('available_end_date')
                                ->label('Available End Date')
                                ->required()
                                ->rule(function (\Filament\Forms\Get $get) {
                                    $start = $get('available_start_date');
                                    return fn($state): ?string =>
                                    $state <= $start ? 'End date must be greater than start date' : null;
                                }),

                            Forms\Components\TextInput::make('session_duration')
                                ->label('Session Duration (minutes)')
                                ->numeric(),

                            Forms\Components\TextInput::make('session_capacity')
                                ->label('Session Capacity')
                                ->numeric()
                                ->minValue(1),
                        ]),

                        // Nested Repeater for serviceBookingDays
                        Repeater::make('serviceBookingDays')
                            ->relationship('serviceBookingDays')
                            ->label('Opening Hours')
                            ->schema([
                                Select::make('day_of_week')
                                    ->label('Day(s) of Week')
                                    ->options([
                                        'Monday' => 'Monday',
                                        'Tuesday' => 'Tuesday',
                                        'Wednesday' => 'Wednesday',
                                        'Thursday' => 'Thursday',
                                        'Friday' => 'Friday',
                                        'Saturday' => 'Saturday',
                                        'Sunday' => 'Sunday',
                                    ])
                                    ->multiple()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                TimePicker::make('opening_time')
                                    ->label('Opening Time')
                                    ->required(),

                                TimePicker::make('closing_time')
                                    ->label('Closing Time')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Opening Hours'),
                    ])
                    ->columns(1)
                    ->columnSpan(1) // adjust width if needed
                    ->addActionLabel('Add Service Booking')
                    ->addable(false),


                // Slug & Tags Section
                Section::make('Slug')
                    ->description('Set the slug and associated tags.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('slug')->label('Slug')->maxLength(255)->placeholder('Please Enter Slug'),
                        ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Repeater::make('requirements')
                    ->label('Requirements Item')
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

                Repeater::make('notes')
                    ->label('Notes')
                    ->relationship('notes')
                    ->schema([
                        Forms\Components\TextInput::make('note')
                            ->label('Note')
                            ->translatable(),
                    ])
                    ->columns(1)
                    ->columnSpan(1),

                // Features Section
                Section::make('Features')
                    ->description('Select available features.')
                    ->schema([
                        CheckboxList::make('Features')->relationship('features', 'name')->columns(4)->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columns(1),

                // Media Uploads Section
                Section::make('Media Uploads')
                    ->description('Upload main and gallery images.')
                    ->schema([
                        Grid::make(1)->schema([
                            SpatieMediaLibraryFileUpload::make('main_service')
                                ->label('Main Image')
                                ->collection('main_service')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('service_gallery')
                                ->label('Gallery Images')
                                ->collection('service_gallery')
                                ->multiple()
                                ->required()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ]),
                    ])
                    ->collapsible()
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
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('region.name')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions(ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                   ]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
        // ->modifyQueryUsing(function (Builder $query) {
        //     $query->whereDoesntHave('topTenPlaces')->whereDoesntHave('popularPlaces');
        // });
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
