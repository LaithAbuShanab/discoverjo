<?php

namespace App\Filament\Host\Resources;

use App\Filament\Host\Resources\PropertyResource\Pages;
use App\Filament\Host\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{CheckboxList, Grid, Hidden, Repeater, Select, SpatieMediaLibraryFileUpload, Textarea, TextInput, TimePicker, Toggle, Wizard, Wizard\Step};
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


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
                ->default([[
                    'day_of_week' => [],
                    'price' => null,
                ]])
                ->disableItemDeletion(function (Get $get) use ($periodType) {
                    $items = $get('availabilityDays_' . $periodType) ?? [];

                    return count($items) <= 1;
                })
                ->minItems(1)
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
                    return collect($periods)->values()->contains(fn($p) => (int) $p['type'] === $periodType);
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

                            TextInput::make('address')->maxLength(255)->translatable(),

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
                                ->minItems(1)
                                ->maxItems(3)
                                ->disableItemDeletion(function (Get $get) {
                                    $items = $get('periods') ?? [];

                                    return count($items) <= 1;
                                })
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
                                ->disableItemDeletion(function (Get $get) {
                                    $items = $get('availabilities') ?? [];

                                    return count($items) <= 1;
                                })
                                ->schema([
                                    Hidden::make('id'),
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
                                            ->rule('after_or_equal:today')
                                            ->native(false)
                                            ->minDate(Carbon::today()->toDateString())
                                            ->displayFormat('d/m/Y'),
                                        Forms\Components\DatePicker::make('availability_end_date')
                                            ->label(__('panel.provider.available-end-date'))
                                            ->required()
                                            ->minDate(fn(Get $get) => $get('availability_start_date'))
                                            ->rule('after_or_equal:availability_start_date')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
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
                                ->label(__('panel.provider.amenities'))
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_guests')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('status')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn(Builder $query) => $query->where('host_id', auth()->id()));
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
