<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Models\Place;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

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
                Section::make('Place Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required()->placeholder('Please Enter Name')->translatable()->columnSpanFull(),
                        Forms\Components\Textarea::make('description')->required()->placeholder('Please Enter Description')->translatable()->columnSpanFull(),
                        Forms\Components\TextInput::make('address')->required()->placeholder('Please Enter Address')->translatable()->columnSpanFull(),
                        Forms\Components\TextInput::make('google_map_url')->required()->columnSpan(1)->placeholder('Please Enter Google Map Url'),
                        Forms\Components\TextInput::make('phone_number')->tel()->maxLength(255)->required()->placeholder('Please Enter Phone Number'),
                        Forms\Components\TextInput::make('longitude')->required()->numeric()->placeholder('EX: 32.123456'),
                        Forms\Components\TextInput::make('latitude')->required()->numeric()->placeholder('EX: 32.123456'),
                        Forms\Components\TextInput::make('price_level')->required()->numeric()->default(-1),
                        Forms\Components\TextInput::make('website')->columnSpan(1)->placeholder('Please Enter Website'),
                        Forms\Components\TextInput::make('rating')->numeric()->required()->placeholder('Please Enter Rating'),
                        Forms\Components\TextInput::make('total_user_rating')->numeric()->required()->placeholder('Please Enter Total User Rating'),
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name', function ($query) {
                                $query->whereNotNull('parent_id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('business_status')->options(self::$statuses)->required()->default('2'),
                        Forms\Components\Select::make('region_id')->relationship('region', 'name')->required(),
                        CheckboxList::make('Features')->relationship('features', 'name')->columnSpanFull()->columns(4),
                        SpatieMediaLibraryFileUpload::make('main_place')->collection('main_place')->columnSpanFull()->required(),
                        SpatieMediaLibraryFileUpload::make('place_gallery')->collection('place_gallery')->columnSpanFull()->multiple()->required()->panelLayout('grid')
                    ])->columnSpan(2)->columns(2),
                Group::make()->schema([
                    Section::make('Slug & Tags')->schema([
                        Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(255)->placeholder('Please Enter Slug'),
                        Select::make('tags')->preload()->relationship('tags', 'name')->multiple()->searchable(),
                    ]),
                    Section::make('openingHours')->schema([
                        Repeater::make('openingHours')
                            ->schema([
                                Select::make('day_of_week')->options([
                                    'Monday' => 'Monday',
                                    'Tuesday' => 'Tuesday',
                                    'Wednesday' => 'Wednesday',
                                    'Thursday' => 'Thursday',
                                    'Friday' => 'Friday',
                                    'Saturday' => 'Saturday',
                                    'Sunday' => 'Sunday',
                                ])->required()->multiple()->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TimePicker::make('opening_time')->required(),
                                TimePicker::make('closing_time')->required(),
                            ])->addActionLabel('Add Opening Hours')
                    ]),

                ]),
            ])->columns([
                'default' => 3,
                'sm' => 3,
                'md' => 3,
                'lg' => 3,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                RatingColumn::make('rating')->theme(RatingTheme::HalfStars)->sortable()->color('warning')->default(0.0),
                Tables\Columns\TextColumn::make('region.name')->searchable()->sortable(),
                SpatieMediaLibraryImageColumn::make('Media')->allCollections()->circular()->stacked()->limit(3)->limitedRemainingText()
            ])
            ->filters([
                //
            ])
            ->actions(ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
