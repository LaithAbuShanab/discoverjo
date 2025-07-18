<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Models\Place;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Set;
use Filament\Tables\Filters\SelectFilter;

class PlaceResource extends Resource
{

    protected static ?string $model = Place::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Discover Jordan';

    protected static array $statuses = [
        '0' => 'Closed',
        '1' => 'Operational',
        '2' => 'Temporary Closed',
        '3' => 'We Do Not Know',
    ];

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Place Details Section
                Section::make('Place Details')
                    ->description('Main information about the place.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')->label('Name')->required()->placeholder('Please Enter Name')->translatable(),
                            TextInput::make('address')->label('Address')->required()->placeholder('Please Enter Address')->translatable(),
                            Textarea::make('description')->label('Description')->rows(5)->required()->placeholder('Please Enter Description')->translatable()->columnSpan(2),
                            TextInput::make('google_map_url')->label('Google Map URL')->required()->placeholder('Enter Google Map URL'),
                            TextInput::make('website')->label('Website')->placeholder('Enter Website'),
                            TextInput::make('phone_number')->label('Phone Number')->tel()->maxLength(255)->placeholder('Enter Phone Number'),
                            TextInput::make('longitude')->label('Longitude')->required()->numeric()->placeholder('Ex: 32.123456'),
                            TextInput::make('latitude')->label('Latitude')->required()->numeric()->placeholder('Ex: 32.123456'),
                            TextInput::make('price_level')->label('Price Level')->required()->numeric()->default(-1),
                            TextInput::make('rating')->label('Rating')->required()->numeric()->placeholder('Enter Rating'),
                            TextInput::make('total_user_rating')->label('Total User Rating')->required()->numeric()->placeholder('Enter Total User Rating'),
                            Select::make('categories')
                                ->label('Categories')
                                ->relationship('categories', 'name', fn($query) => $query->whereNotNull('parent_id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),
                            Select::make('business_status')->label('Business Status')->options(self::$statuses)->required()->default('2'),
                            Select::make('region_id')->label('Region')->relationship('region', 'name')->required(),
                            Toggle::make('status')->label('Status')->required(),
                        ])
                    ])
                    ->collapsible()
                    ->columns(1),

                // Slug & Tags Section
                Section::make('Slug & Tags')
                    ->description('Set the slug and associated tags.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('slug')->label('Slug')->maxLength(255)->placeholder('Please Enter Slug'),
                            Select::make('tags')->label('Tags')->relationship('tags', 'name')->multiple()->searchable()->preload(),
                        ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                // Opening Hours Section
                Section::make('Opening Hours')
                    ->description('Specify working days and hours.')
                    ->schema([
                        Repeater::make('openingHours')
                            ->label('Opening Hours')
                            ->schema([
                                Select::make('day_of_week')->label('Day(s) of Week')->options([
                                    'Monday' => 'Monday',
                                    'Tuesday' => 'Tuesday',
                                    'Wednesday' => 'Wednesday',
                                    'Thursday' => 'Thursday',
                                    'Friday' => 'Friday',
                                    'Saturday' => 'Saturday',
                                    'Sunday' => 'Sunday',
                                ])->multiple()->required()->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TimePicker::make('opening_time')->label('Opening Time')->required(),
                                TimePicker::make('closing_time')->label('Closing Time')->required(),
                            ])->addActionLabel('Add Opening Hours'),
                    ])
                    ->collapsible()
                    ->columns(1),

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
                            SpatieMediaLibraryFileUpload::make('main_place')
                                ->label('Main Image')
                                ->collection('main_place')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('place_gallery')
                                ->label('Gallery Images')
                                ->collection('place_gallery')
                                ->multiple()
                                ->required()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ]),
                    ])
                    ->collapsible()
                    ->columns(1),


                Section::make('Map Location')
                    ->description('Displays the current location and allows editing.')
                    ->schema([
                        Grid::make(1)->schema([
                            Map::make('location')
                                ->label('Location')
                                ->zoom(12)
                                ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                                ->afterStateHydrated(function ($state, $record, Set $set): void {
                                    if ($record) {
                                        $set('location', [
                                            'lat' => $record->latitude,
                                            'lng' => $record->longitude,
                                            'geojson' => json_decode(strip_tags($record->description ?? '{}')),
                                        ]);
                                    }
                                }),
                        ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->visible(fn($livewire) => $livewire->getRecord() !== null),

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
                Tables\Columns\TextColumn::make('total_user_rating')->sortable(),
                RatingColumn::make('rating')->theme(RatingTheme::HalfStars)->sortable()->color('warning')->default(0.0),
                Tables\Columns\TextColumn::make('region.name')->searchable()->sortable(),
            ])
            ->filters([
                SelectFilter::make('region')->relationship('region', 'name')->multiple()->searchable()->preload(),
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->searchable()->preload(),
            ])
            ->actions(ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->google_place_id) {
                            DB::table('trash_places')->insert([
                                'google_place_id' => $record->google_place_id,
                            ]);
                        }
                    }),
                ActivityLogTimelineTableAction::make('Activities'),
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
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
