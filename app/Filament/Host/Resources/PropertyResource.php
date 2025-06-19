<?php

namespace App\Filament\Host\Resources;

use App\Filament\Host\Resources\PropertyResource\Pages;
use App\Filament\Host\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use App\Models\PropertyAvailability;
use App\Models\PropertyAvailabilityDay;
use App\Models\PropertyPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Service;
use Filament\Forms\Components\{CheckboxList, Grid, Repeater, Select, SpatieMediaLibraryFileUpload, Textarea, TextInput, TimePicker, Toggle, Wizard, Wizard\Step};
use Filament\Forms\Get;
use Filament\Tables\Actions\ActionGroup;
use Closure;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;




class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Chalet Section';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('host_id', auth()->id())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {

        $periodRepeaterFor = function (int $periodType, string $labelKey) {
            return \Filament\Forms\Components\Repeater::make("availabilityDays_{$periodType}")
                ->label(__("panel.provider.availability-for") . ' ' . __("panel.provider.{$labelKey}"))
                ->schema([
                    \Filament\Forms\Components\Hidden::make('property_period_id')
                        ->default($periodType),

                    \Filament\Forms\Components\Select::make('day_of_week')
                        ->label(__('panel.provider.day-of-week'))
                        ->options([
                            'Monday' => __('panel.provider.monday'),
                            'Tuesday' => __('panel.provider.tuesday'),
                            'Wednesday' => __('panel.provider.wednesday'),
                            'Thursday' => __('panel.provider.thursday'),
                            'Friday' => __('panel.provider.friday'),
                            'Saturday' => __('panel.provider.saturday'),
                            'Sunday' => __('panel.provider.sunday'),
                        ])
                        ->multiple()
                        ->required()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                    \Filament\Forms\Components\TextInput::make('price')
                        ->label(__('panel.provider.price'))
                        ->numeric()
                        ->minValue(0)
                        ->step(0.1)
                        ->required(),
                ])
                ->visible(function (Get $get) use ($periodType) {
                    $periods = $get('../../periods') ?? [];

                    return collect($periods)->contains(fn ($p) => (int) $p['type'] === $periodType);
                });
        };

        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Basic Info
                    Step::make(__('panel.provider.basic-info'))
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->translatable(),

                            Textarea::make('description')
                                ->columnSpanFull()
                                ->translatable(),

                            Select::make('region_id')
                                ->label(__('panel.host.region'))
                                ->relationship('region', 'name')
                                ->required()
                                ->placeholder(__('panel.host.select-region')),

                            TextInput::make('address')->maxLength(255),

                            TextInput::make('google_map_url')
                                ->label(__('panel.host.google-map-url'))
                                ->required()
                                ->placeholder(__('panel.host.enter-google-map-url'))
                                ->url(),

                            TextInput::make('max_guests')->required()->numeric()->minValue(1),
                            TextInput::make('bedrooms')->required()->numeric()->minValue(1),
                            TextInput::make('bathrooms')->required()->numeric()->minValue(1),
                            TextInput::make('beds')->required()->numeric()->minValue(1),

                            Toggle::make('status')
                                ->label(__('panel.provider.status'))
                                ->required()
                                ->inline(false),
                        ]),

                    // Step 2: Property Periods
                    Step::make(__('panel.provider.periods'))
                        ->schema([
                            Repeater::make('periods')
                                ->relationship('periods')
                                ->label(__('panel.provider.property-periods'))
                                ->schema([
                                    Select::make('type')
                                        ->required()
                                        ->options([
                                            1 => 'morning',
                                            2 => 'evening',
                                            3 => 'overnight',
                                        ])
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                    TimePicker::make('start_time')
                                        ->label(__('panel.provider.opening-time'))
                                        ->required(),
                                    TimePicker::make('end_time')
                                        ->label(__('panel.provider.closing-time'))
                                        ->required(),
                                ])
                                ->addActionLabel(__('panel.provider.add-service-booking-day'))
                                ->required(),
                        ]),

                    // Step 3: Availabilities
                    Step::make(__('panel.provider.available-services'))
                        ->schema([
                            Repeater::make('availabilities')
                                ->relationship('availabilities')
                                ->label(__('panel.provider.availabilities'))
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 3])->schema([
                                        Select::make('type')
                                            ->required()
                                            ->options([
                                                1 => 'basic',
                                                2 => 'seasonal',
                                                3 => 'eid',
                                            ]),
                                        Forms\Components\DatePicker::make('availability_start_date')
                                            ->label(__('panel.provider.available-start-date'))
                                            ->required()
                                            ->rule('after_or_equal:today'),
                                        Forms\Components\DatePicker::make('availability_end_date')
                                            ->label(__('panel.provider.available-end-date'))
                                            ->required()
                                            ->minDate(fn (Get $get) => $get('availability_start_date'))
                                            ->rule('after_or_equal:availability_start_date'),
                                    ]),


                                    $periodRepeaterFor(1, 'morning'),
                                    $periodRepeaterFor(2, 'evening'),
                                    $periodRepeaterFor(3, 'overnight'),

                                ])
                                ->addActionLabel(__('panel.provider.add-service-booking'))
                                ->columns(1),
                        ]),

                    // Step 4: Notes
                    Step::make(__('panel.provider.activities-and-notes'))
                        ->schema([
                            Repeater::make('notes')
                                ->defaultItems(0)
                                ->label(__('panel.provider.notes'))
                                ->relationship('notes')
                                ->schema([
                                    TextInput::make('note')
                                        ->label(__('panel.provider.note'))
                                        ->placeholder(__('panel.provider.enter-note'))
                                        ->required()
                                        ->translatable(),
                                ])
                                ->columns(1),
                        ]),

                    // Step 5: Media & Amenities
                    Step::make(__('panel.provider.media-and-amenities'))
                        ->schema([
                            CheckboxList::make('amenities') // ← lowercase to match the Eloquent relationship method name
                            ->label(__('panel.provider.features'))
                                ->relationship('amenities', 'name')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'lg' => 4,
                                ])
                                ->columnSpanFull()
                                ->required(),

                            SpatieMediaLibraryFileUpload::make('main_property_image')
                                ->label(__('panel.provider.main-image'))
                                ->collection('main_property_image')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('property_gallery')
                                ->label(__('panel.provider.service-gallery'))
                                ->collection('property_gallery')
                                ->multiple()
                                ->required()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ]),
                ]),
            ])
            ->columns(1);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('host_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_guests')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }


}
