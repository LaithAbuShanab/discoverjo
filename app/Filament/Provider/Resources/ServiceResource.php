<?php

namespace App\Filament\Provider\Resources;

use App\Filament\Provider\Resources\ServiceResource\Pages;
use App\Filament\Provider\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\{CheckboxList, Grid, Repeater, Select, SpatieMediaLibraryFileUpload, Textarea, TextInput, TimePicker, Toggle, Wizard, Wizard\Step};
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $navigationGroup = 'Services Section';

    public static function getNavigationLabel(): string
    {
        return __('panel.provider.services');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.provider.services');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('provider_type', 'App\Models\User')->where('provider_id', auth()->id())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make(__('panel.provider.basic-info'))
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')->label(__('panel.provider.name'))->required()->placeholder(__('panel.provider.enter-name'))->translatable(),
                                TextInput::make('address')->label(__('panel.provider.address'))->required()->placeholder(__('panel.provider.enter-address'))->translatable(),
                                Textarea::make('description')->label(__('panel.provider.description'))->rows(5)->required()->placeholder(__('panel.provider.enter-description'))->translatable()->columnSpan(2),
                                TextInput::make('url_google_map')->label(__('panel.provider.google-map-url'))->required()->placeholder(__('panel.provider.enter-google-map-url'))->url(),
                                Select::make('categories')->label(__('panel.provider.categories'))->relationship('categories', 'name', fn($query) => $query->whereNotNull('parent_id'))->placeholder(__('panel.provider.select-category'))->multiple()->searchable()->preload()->required(),
                                Select::make('region_id')->label(__('panel.provider.region'))->relationship('region', 'name')->required()->placeholder(__('panel.provider.select-region')),
                                TextInput::make('price')->label(__('panel.provider.price'))->placeholder(__('panel.provider.enter-price'))->nullable()->numeric()->required(),
                                Toggle::make('status')->label(__('panel.provider.status'))->required()->inline(false),
                            ])
                        ]),

                    Step::make(__('panel.provider.available-services'))
                        ->schema([
                            Repeater::make('serviceBookings')
                                ->label(__('panel.provider.available-services'))
                                ->relationship('serviceBookings')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Forms\Components\DatePicker::make('available_start_date')
                                            ->label(__('panel.provider.available-start-date'))
                                            ->required()
                                            ->rule('after_or_equal:today'),

                                        Forms\Components\DatePicker::make('available_end_date')
                                            ->label(__('panel.provider.available-end-date'))
                                            ->required()
                                            ->minDate(fn(Get $get) => $get('available_start_date'))
                                            ->rule('after_or_equal:available_start_date'),

                                        TextInput::make('session_duration')
                                            ->label(__('panel.provider.session-duration'))
                                            ->placeholder(__('panel.provider.enter-session-duration'))
                                            ->numeric()->required(),
                                        TextInput::make('session_capacity')
                                            ->label(__('panel.provider.session-capacity'))
                                            ->placeholder(__('panel.provider.enter-session-capacity'))
                                            ->numeric()->minValue(1)->required(),
                                    ]),

                                    Repeater::make('serviceBookingDays')
                                        ->relationship('serviceBookingDays')
                                        ->label(__('panel.provider.service-booking-days'))
                                        ->schema([
                                            Select::make('day_of_week')
                                                ->label(__('panel.provider.day-of-week'))
                                                ->placeholder(__('panel.provider.select-day-of-week'))
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

                                            TimePicker::make('opening_time')->label(__('panel.provider.opening-time'))->required(),
                                            TimePicker::make('closing_time')->label(__('panel.provider.closing-time'))->required(),
                                        ])
                                        ->addActionLabel(__('panel.provider.add-service-booking-day'))
                                        ->required(),
                                ])
                                ->columns(1)
                                ->columnSpan(1)
                                ->addActionLabel(__('panel.provider.add-service-booking'))
                                ->addable(false),
                        ]),

                    Step::make(__('panel.provider.price-info-and-requirements'))
                        ->schema([

                            Repeater::make('requirements')
                                ->defaultItems(0)
                                ->label(__('panel.provider.requirements'))
                                ->relationship('requirements')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('item')->label(__('panel.provider.item'))->placeholder(__('panel.provider.enter-item'))->required()->translatable(),
                                    ]),
                                ])
                                ->columns(1),

                            Repeater::make('priceAges')
                                ->label(__('panel.provider.price-ages'))
                                ->defaultItems(0)
                                ->relationship('priceAges')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('min_age')->label(__('panel.provider.min-age'))->placeholder(__('panel.provider.enter-min-age'))->numeric()->minValue(0)->required(),
                                        TextInput::make('max_age')->label(__('panel.provider.max-age'))->placeholder(__('panel.provider.enter-max-age'))->numeric()->minValue(0)->required()->minValue(fn(Forms\Get $get) => $get('min_age')),
                                        TextInput::make('price')->label(__('panel.provider.price'))->placeholder(__('panel.provider.enter-price'))->numeric()->minValue(0)->step(0.1)->required(),
                                    ]),
                                ])
                                ->columns(1),
                        ]),

                    Step::make(__('panel.provider.activities-and-notes'))
                        ->schema([
                            Repeater::make('activities')
                                ->label(__('panel.provider.activities'))
                                ->relationship('activities')
                                ->schema([
                                    TextInput::make('activity')->label(__('panel.provider.activity'))->placeholder(__('panel.provider.enter-activity'))->required()->translatable(),
                                ])
                                ->columns(1)
                                ->required(),

                            Repeater::make('notes')
                                ->defaultItems(0)
                                ->label(__('panel.provider.notes'))
                                ->relationship('notes')
                                ->schema([
                                    TextInput::make('note')->label(__('panel.provider.note'))->placeholder(__('panel.provider.enter-note'))->required()->translatable(),
                                ])
                                ->columns(1),
                        ]),

                    Step::make(__('panel.provider.media-and-features'))
                        ->schema([
                            CheckboxList::make('Features')
                                ->label(__('panel.provider.features'))
                                ->relationship('features', 'name')
                                ->columns(4)
                                ->columnSpanFull()
                                ->required(),

                            SpatieMediaLibraryFileUpload::make('main_service')
                                ->label(__('panel.provider.main-image'))
                                ->collection('main_service')
                                ->required()
                                ->columnSpanFull(),

                            SpatieMediaLibraryFileUpload::make('service_gallery')
                                ->label(__('panel.provider.service-gallery'))
                                ->collection('service_gallery')
                                ->multiple()
                                ->required()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ]),
                ])
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('panel.provider.id'))->searchable(),
                Tables\Columns\TextColumn::make('name')->label(__('panel.provider.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('region.name')->label(__('panel.provider.region'))->searchable()->sortable(),
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
            ])->modifyQueryUsing(fn(Builder $query) => $query->where('provider_type', 'App\Models\User')->where('provider_id', auth()->id()));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReservationsRelationManager::class,
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
