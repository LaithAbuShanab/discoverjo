<?php

namespace App\Filament\provider\Resources;

use App\Filament\Provider\Resources\GuideTripResource\{Pages, RelationManagers\GuideTripUsersRelationManager};
use App\Models\{GuideTrip, User};
use Carbon\Carbon;
use Filament\Forms\Components\{
    DateTimePicker,
    Grid,
    Repeater,
    Select,
    SpatieMediaLibraryFileUpload,
    Textarea,
    TextInput,
    TimePicker,
    Toggle,
    ToggleButtons,
    Wizard,
    Wizard\Step
};
use Filament\Forms\{Form, Get};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GuideTripResource extends Resource
{
    protected static ?string $model = GuideTrip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Guide Trips';

    public static function canAccess(): bool
    {
        $user = User::find(Auth::guard('provider')->id());
        if ($user->userTypes()->where('type', 2)->exists()) {
            return true;
        }
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.guide.trips');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.guide.trips');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('guide_id', auth()->id())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make(__('panel.guide.general-and-schedule'))
                        ->schema([
                            Grid::make(['default' => 1])->schema([
                                TextInput::make('name')
                                    ->label(__('panel.guide.trip-name'))
                                    ->placeholder(__('panel.guide.enter-trip-name'))
                                    ->required()
                                    ->translatable()
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label(__('panel.guide.trip-description'))
                                    ->placeholder(__('panel.guide.enter-trip-description'))
                                    ->rows(4)
                                    ->required()
                                    ->translatable()
                                    ->columnSpanFull(),
                            ]),

                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                DateTimePicker::make('start_datetime')
                                    ->label(__('panel.guide.start-date'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('end_datetime', null))
                                    ->rules(function ($component) {
                                        $record = $component->getRecord();
                                        if ($record) {
                                            return [];
                                        }
                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        return ["after_or_equal:$now"];
                                    }),

                                DateTimePicker::make('end_datetime')
                                    ->label(__('panel.guide.end-date'))
                                    ->required()
                                    ->reactive()
                                    ->rules(['after:start_datetime']),
                            ]),
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                SpatieMediaLibraryFileUpload::make('main_image')
                                    ->label(__('panel.guide.main-image'))
                                    ->collection('main_image')
                                    ->required()
                                    ->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('guide_trip_gallery')
                                    ->label(__('panel.guide.guide-trip-gallery'))
                                    ->collection('guide_trip_gallery')
                                    ->multiple()
                                    ->required()
                                    ->columnSpanFull()
                                    ->panelLayout('grid'),
                            ]),
                        ]),

                    Step::make(__('panel.guide.pricing-and-capacity'))
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('main_price')
                                    ->label(__('panel.guide.main-price'))
                                    ->placeholder(__('panel.guide.enter-main-price'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(999.99),

                                TextInput::make('max_attendance')
                                    ->label(__('panel.guide.max-attendance'))
                                    ->placeholder(__('panel.guide.enter-max-attendance'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),

                                Select::make('region_id')
                                    ->label(__('panel.guide.region'))
                                    ->placeholder(__('panel.guide.select-region'))
                                    ->relationship('region', 'name')
                                    ->required(),

                                Toggle::make('status')
                                    ->label(__('panel.guide.status'))
                                    ->required()
                                    ->inline(false),
                            ]),
                        ]),

                    Step::make(__('panel.guide.trip-details'))
                        ->schema([
                            Repeater::make('activities')
                                ->label(__('panel.guide.activities'))
                                ->relationship('activities')
                                ->schema([
                                    TextInput::make('activity')
                                        ->label(__('panel.guide.activity'))
                                        ->placeholder(__('panel.guide.enter-activity'))
                                        ->required()
                                        ->translatable(),
                                ])
                                ->columns(1)
                                ->minItems(1)
                                ->required(),

                            Repeater::make('assemblies')
                                ->label(__('panel.guide.assemblies'))
                                ->relationship('assemblies')
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 2])->schema([
                                        TextInput::make('place')
                                            ->label(__('panel.guide.place'))
                                            ->placeholder(__('panel.guide.enter-place'))
                                            ->required()
                                            ->translatable(),

                                        TimePicker::make('time')
                                            ->label(__('panel.guide.time'))
                                            ->required(),
                                    ]),
                                ])
                                ->columns(1),

                            Repeater::make('priceAges')
                                ->label(__('panel.guide.price-ages'))
                                ->relationship('priceAges')
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 3])->schema([
                                        TextInput::make('min_age')
                                            ->label(__('panel.guide.min-age'))
                                            ->placeholder(__('panel.guide.enter-min-age'))
                                            ->numeric()
                                            ->minValue(0),

                                        TextInput::make('max_age')
                                            ->label(__('panel.guide.max-age'))
                                            ->placeholder(__('panel.guide.enter-max-age'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->rule(function (Get $get) {
                                                $minAge = $get('min_age');
                                                return fn($state): ?string => $state <= $minAge ? 'Max age must be greater than min age.' : null;
                                            }),

                                        TextInput::make('price')
                                            ->label(__('panel.guide.price'))
                                            ->placeholder(__('panel.guide.enter-price'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.1),
                                    ]),
                                ])
                                ->columns(1),

                            Repeater::make('priceIncludes')
                                ->label(__('panel.guide.price-includes'))
                                ->relationship('priceIncludes')
                                ->schema([
                                    TextInput::make('include')
                                        ->label(__('panel.guide.include'))
                                        ->placeholder(__('panel.guide.enter-include'))
                                        ->required()
                                        ->translatable(),
                                ])
                                ->columns(1),

                            Repeater::make('requirements')
                                ->label(__('panel.guide.requirements'))
                                ->relationship('requirements')
                                ->schema([
                                    TextInput::make('item')
                                        ->label(__('panel.guide.item'))
                                        ->placeholder(__('panel.guide.enter-item'))
                                        ->translatable(),
                                ])
                                ->columns(1),

                            Repeater::make('payment_method')
                                ->label(__('panel.guide.payment-method'))
                                ->relationship('paymentMethods')
                                ->schema([
                                    TextInput::make('method')
                                        ->label(__('panel.guide.method'))
                                        ->placeholder(__('panel.guide.enter-method'))
                                        ->required()
                                        ->translatable(),
                                ])
                                ->columns(1),
                        ]),

                    Step::make(__('panel.guide.trail'))
                        ->schema([
                            Toggle::make('is_trail')
                                ->label(__('panel.guide.is-trail'))
                                ->live()
                                ->afterStateHydrated(function (Toggle $component, $state) {
                                    $record = $component->getRecord();
                                    $component->state((bool) $record?->trail);
                                }),

                            Grid::make(['default' => 1, 'md' => 2, 'lg' => 4])->schema([
                                TextInput::make('min_duration_in_minute')
                                    ->label(__('panel.guide.min-duration'))
                                    ->placeholder(__('panel.guide.enter-min-duration'))
                                    ->numeric()
                                    ->nullable()
                                    ->minValue(0)
                                    ->maxValue(999)
                                    ->required(fn(Get $get) => $get('is_trail'))
                                    ->afterStateHydrated(function ($component) {
                                        $trail = $component->getRecord()?->trail;
                                        if ($trail) $component->state($trail->min_duration_in_minute);
                                    }),

                                TextInput::make('max_duration_in_minute')
                                    ->label(__('panel.guide.max-duration'))
                                    ->placeholder(__('panel.guide.enter-max-duration'))
                                    ->numeric()
                                    ->nullable()
                                    ->minValue(fn(Get $get) => $get('min_duration_in_minute'))
                                    ->maxValue(999)
                                    ->required(fn(Get $get) => $get('is_trail'))
                                    ->afterStateHydrated(function ($component) {
                                        $trail = $component->getRecord()?->trail;
                                        if ($trail) $component->state($trail->max_duration_in_minute);
                                    }),

                                TextInput::make('distance_in_meter')
                                    ->label(__('panel.guide.distance'))
                                    ->placeholder(__('panel.guide.enter-distance'))
                                    ->numeric()
                                    ->nullable()
                                    ->minValue(0)
                                    ->maxValue(99999999.99)
                                    ->required(fn(Get $get) => $get('is_trail'))
                                    ->afterStateHydrated(function ($component) {
                                        $trail = $component->getRecord()?->trail;
                                        if ($trail) $component->state($trail->distance_in_meter);
                                    }),

                                ToggleButtons::make('difficulty')
                                    ->label(__('panel.guide.difficulty'))
                                    ->inline()
                                    ->options([
                                        0 => __('panel.guide.easy'),
                                        1 => __('panel.guide.moderate'),
                                        2 => __('panel.guide.hard'),
                                        3 => __('panel.guide.very-hard'),
                                    ])
                                    ->required(fn(Get $get) => $get('is_trail'))
                                    ->afterStateHydrated(function ($component) {
                                        $trail = $component->getRecord()?->trail;
                                        if ($trail) $component->state($trail->difficulty);
                                    })
                                    ->columnSpanFull(),
                            ])
                                ->visible(fn(Get $get) => $get('is_trail')),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('panel.guide.trip-name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->label(__('panel.guide.start-date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_datetime')
                    ->label(__('panel.guide.end-date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('main_price')
                    ->label(__('panel.guide.main-price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_attendance')
                    ->label(__('panel.guide.max-attendance'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('status')
                    ->label(__('panel.guide.status'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('panel.guide.created-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('panel.guide.updated-at'))
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
            GuideTripUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideTrips::route('/'),
            'create' => Pages\CreateGuideTrip::route('/create'),
            'edit' => Pages\EditGuideTrip::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('guide_id', auth()->id());
    }
}
