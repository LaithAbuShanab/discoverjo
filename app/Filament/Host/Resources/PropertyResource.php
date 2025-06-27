<?php

namespace App\Filament\Host\Resources;

use App\Filament\Host\Resources\PropertyResource\Pages;
use App\Filament\Host\Resources\PropertyResource\RelationManagers\ReservationsRelationManager;
use App\Models\Property;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{CheckboxList, DatePicker, Grid, Hidden, Repeater, Select, SpatieMediaLibraryFileUpload, Textarea, TextInput, TimePicker, Toggle, Wizard, Wizard\Step};
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Chalet Section';

    public static function getNavigationLabel(): string
    {
        return __('panel.host.properties');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.host.properties');
    }

    public static function getModelLabel(): string
    {
        return __('panel.host.property');
    }

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
            return Repeater::make("availabilityDays_{$periodType}")
                ->label(__('panel.host.availability-for') . ' ' . __('panel.host.' . $labelKey))
                ->default([
                    [
                        'day_of_week' => [],
                        'price' => null,
                    ],
                ])
                ->minItems(1)
                ->disableItemDeletion(function (Get $get) use ($periodType) {
                    $items = $get("availabilityDays_{$periodType}") ?? [];
                    return count($items) <= 1;
                })
                ->schema([
                    Hidden::make('property_period_id')->default($periodType),

                    Select::make('day_of_week')
                        ->label(__('panel.host.day-of-week'))
                        ->placeholder(__('panel.host.select-days'))
                        ->options([
                            'Monday'    => __('panel.host.monday'),
                            'Tuesday'   => __('panel.host.tuesday'),
                            'Wednesday' => __('panel.host.wednesday'),
                            'Thursday'  => __('panel.host.thursday'),
                            'Friday'    => __('panel.host.friday'),
                            'Saturday'  => __('panel.host.saturday'),
                            'Sunday'    => __('panel.host.sunday'),
                        ])

                        ->multiple()
                        ->required()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                    TextInput::make('price')
                        ->label(__('panel.host.price'))
                        ->placeholder(__('panel.host.enter-price'))
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

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('panel.host.name'))
                                        ->placeholder(__('panel.host.enter-name'))
                                        ->required()
                                        ->translatable(),

                                    TextInput::make('address')
                                        ->label(__('panel.host.address'))
                                        ->placeholder(__('panel.host.enter-address'))
                                        ->maxLength(255)
                                        ->translatable(),
                                ]),

                            Textarea::make('description')
                                ->label(__('panel.host.description'))
                                ->placeholder(__('panel.host.enter-description'))
                                ->columnSpanFull()
                                ->translatable(),

                            Grid::make(2)
                                ->schema([
                                    Select::make('region_id')
                                        ->label(__('panel.host.region'))
                                        ->relationship('region', 'name')
                                        ->required()
                                        ->placeholder(__('panel.host.select-region')),

                                    TextInput::make('google_map_url')
                                        ->label(__('panel.host.google-map-url'))
                                        ->placeholder(__('panel.host.enter-google-map-url'))
                                        ->required()
                                        ->url(),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('max_guests')
                                        ->label(__('panel.host.max-guests'))
                                        ->placeholder(__('panel.host.enter-max-guests'))
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),

                                    TextInput::make('bedrooms')
                                        ->label(__('panel.host.bedrooms'))
                                        ->placeholder(__('panel.host.enter-bedrooms'))
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('bathrooms')
                                        ->label(__('panel.host.bathrooms'))
                                        ->placeholder(__('panel.host.enter-bathrooms'))
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),

                                    TextInput::make('beds')
                                        ->label(__('panel.host.beds'))
                                        ->placeholder(__('panel.host.enter-beds'))
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),
                                ]),

                            Toggle::make('status')
                                ->label(__('panel.host.status'))
                                ->required()
                                ->inline(false),

                        ]),

                    // Step 2: Property Periods
                    Step::make(__('panel.host.periods'))
                        ->schema([
                            Repeater::make('periods')
                                ->label(__('panel.host.property-periods'))
                                ->relationship('periods')
                                ->minItems(1)
                                ->maxItems(3)
                                ->required()
                                ->addActionLabel(__('panel.host.add-service-booking-day'))
                                ->disableItemDeletion(function (Get $get) {
                                    $items = $get('periods') ?? [];

                                    return count($items) <= 1;
                                })
                                ->schema([
                                    Select::make('type')
                                        ->label(__('panel.host.period-type'))
                                        ->placeholder(__('panel.host.select-period-type'))
                                        ->required()
                                        ->options([
                                            1 => __('panel.host.morning'),
                                            2 => __('panel.host.evening'),
                                            3 => __('panel.host.overnight'),
                                        ])
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                    Grid::make(2)
                                        ->schema([
                                            TimePicker::make('start_time')
                                                ->label(__('panel.host.opening-time'))
                                                ->placeholder(__('panel.host.enter-opening-time'))
                                                ->required(),

                                            TimePicker::make('end_time')
                                                ->label(__('panel.host.closing-time'))
                                                ->placeholder(__('panel.host.enter-closing-time'))
                                                ->required(),
                                        ])
                                ]),
                        ]),

                    // Step 3: Availabilities
                    Step::make(__('panel.host.available-services'))
                        ->schema([
                            Repeater::make('availabilities')
                                ->label(__('panel.host.availabilities'))
                                ->relationship('availabilities')
                                ->addActionLabel(__('panel.host.add-service-booking'))
                                ->columns(1)
                                ->disableItemDeletion(function (Get $get) {
                                    $items = $get('availabilities') ?? [];

                                    return count($items) <= 1;
                                })
                                ->schema([
                                    Hidden::make('id'),

                                    Grid::make(['default' => 1, 'md' => 3])
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('panel.host.availability-type'))
                                                ->placeholder(__('panel.host.select-availability-type'))
                                                ->required()
                                                ->options([
                                                    1 => __('panel.host.basic'),
                                                    2 => __('panel.host.seasonal'),
                                                    3 => __('panel.host.eid'),
                                                ]),

                                            DatePicker::make('availability_start_date')
                                                ->label(__('panel.host.available-start-date'))
                                                ->placeholder(__('panel.host.enter-start-date'))
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->rules(function ($livewire) {
                                                    $record = $livewire->getRecord();
                                                    if ($record) {
                                                        return [];
                                                    }
                                                    $now = Carbon::now()->format('Y-m-d');
                                                    return ["after_or_equal:$now"];
                                                })
                                                ->reactive(),

                                            DatePicker::make('availability_end_date')
                                                ->label(__('panel.host.available-end-date'))
                                                ->placeholder(__('panel.host.enter-end-date'))
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->minDate(fn(Get $get) => $get('availability_start_date'))
                                                ->rules(function (Get $get) {
                                                    $now = $get('availability_start_date');
                                                    return ["after:$now"];
                                                }),
                                        ]),

                                    // Morning
                                    $periodRepeaterFor(1, 'morning'),

                                    // Evening
                                    $periodRepeaterFor(2, 'evening'),

                                    // Overnight
                                    $periodRepeaterFor(3, 'overnight'),
                                ]),
                        ]),

                    // Step 4: Notes
                    Step::make(__('panel.host.notes'))
                        ->schema([
                            Repeater::make('notes')
                                ->label(__('panel.host.notes'))
                                ->relationship('notes')
                                ->defaultItems(0)
                                ->columns(1)
                                ->schema([
                                    TextInput::make('note')
                                        ->label(__('panel.host.note'))
                                        ->placeholder(__('panel.host.enter-note'))
                                        ->required()
                                        ->translatable(),
                                ]),
                        ]),

                    // Step 5: Media & Amenities
                    Step::make(__('panel.host.media-and-amenities'))
                        ->schema([
                            CheckboxList::make('amenities')
                                ->label(__('panel.host.amenities'))
                                ->relationship('amenities', 'name')
                                ->columns([
                                    'default' => 1,
                                    'md'      => 2,
                                    'lg'      => 4,
                                ])
                                ->columnSpanFull()
                                ->required(),

                            SpatieMediaLibraryFileUpload::make('main_property_image')
                                ->label(__('panel.host.main-image'))
                                ->collection('main_property_image')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('property_gallery')
                                ->label(__('panel.host.service-gallery'))
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
                    ->label(__('panel.host.name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('region.name')
                    ->label(__('panel.host.region'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->label(__('panel.host.address'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('max_guests')
                    ->label(__('panel.host.max-guests'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('panel.host.status'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => __('panel.host.inactive'),
                        1 => __('panel.host.active'),
                        default => __('panel.host.unknown'),
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        0 => 'danger',
                        1 => 'success',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('panel.host.created-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('panel.host.updated-at'))
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
            ReservationsRelationManager::class,
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
