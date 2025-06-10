<?php

namespace App\Filament\Provider\Resources;

use App\Models\Service;
use App\Filament\Resources\ServiceResource\Pages;
use Filament\Forms\Components\{Wizard, Wizard\Step, Grid, TextInput, Textarea, Select, Toggle, Repeater, CheckboxList, TimePicker};
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

    protected static ?string $navigationGroup = 'Services sections';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('provider_type','App\Models\User')->where('provider_id', auth()->id())->count();
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
                    Step::make('Service Details')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')->label('Name')->required()->placeholder('Please Enter Name')->placeholder('Please Enter Name')->translatable(),
                                TextInput::make('address')->label('Address')->required()->placeholder('Please Enter Address')->placeholder('Please Enter Address')->translatable(),
                                Textarea::make('description')->label('Description')->rows(5)->required()->placeholder('Please Enter Description')->translatable()->columnSpan(2),
                                TextInput::make('url_google_map')->label('Google Map URL')->required()->placeholder('Enter Google Map URL')->url(),
                                Select::make('categories')->label('Categories')->relationship('categories', 'name', fn($query) => $query->whereNotNull('parent_id'))->placeholder('Please Select Category')->multiple()->searchable()->preload()->required(),
                                Select::make('region_id')->label('Region')->relationship('region', 'name')->required()->placeholder('Please Select Region'),
                                TextInput::make('price')->placeholder('Please Enter Price')->nullable()->numeric()->required(),
                                Toggle::make('status')->label('Status')->required()->inline(false),
                            ])
                        ]),

                    Step::make('Available Services')
                        ->schema([
                            Repeater::make('serviceBookings')
                                ->label('Available Services')
                                ->relationship('serviceBookings')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Forms\Components\DatePicker::make('available_start_date')->label('Available Start Date')
                                            ->required()
                                            ->rule('after_or_equal:today'),

                                        Forms\Components\DatePicker::make('available_end_date')->label('Available End Date')
                                            ->required()
                                            ->minDate(fn(Get $get) => $get('available_start_date'))
                                            ->rule('after_or_equal:available_start_date'),

                                        TextInput::make('session_duration')->label('Session Duration (minutes)')->numeric()->required(),
                                        TextInput::make('session_capacity')->label('Session Capacity')->numeric()->minValue(1)->required(),
                                    ]),

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

                                            TimePicker::make('opening_time')->label('Opening Time')->required(),
                                            TimePicker::make('closing_time')->label('Closing Time')->required(),
                                        ])
                                        ->addActionLabel('Add Opening Hours')
                                        ->required(),
                                ])
                                ->columns(1)
                                ->columnSpan(1)
                                ->addActionLabel('Add Service Booking')
                                ->addable(false),
                        ]),

                    Step::make('Requirements & Pricing')
                        ->schema([

                            Repeater::make('requirements')
                                ->defaultItems(0)
                                ->label('Requirements Item')
                                ->relationship('requirements')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextInput::make('item')->label('Item')->required()->translatable(),
                                    ]),
                                ])
                                ->columns(1),

                            Repeater::make('priceAges')
                                ->label('Price Ages')
                                ->defaultItems(0)
                                ->relationship('priceAges')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('min_age')->label('Min Age')->numeric()->minValue(0)->required(),
                                        TextInput::make('max_age')
                                            ->label('Max Age')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->minValue(fn(Forms\Get $get) => $get('min_age')),
                                        TextInput::make('price')->label('Price')->numeric()->minValue(0)->step(0.1)->required(),
                                    ]),
                                ])
                                ->columns(1),
                        ]),

                    Step::make('Activities & Notes')
                        ->schema([
                            Repeater::make('activities')
                                ->label('Activities')
                                ->relationship('activities')
                                ->schema([
                                    TextInput::make('activity')->label('Activity')->required()->translatable(),
                                ])
                                ->columns(1)
                                ->required(),

                            Repeater::make('notes')
                                ->defaultItems(0)
                                ->label('Notes')
                                ->relationship('notes')
                                ->schema([
                                    TextInput::make('note')->label('Note')->required()->translatable(),
                                ])
                                ->columns(1),
                        ]),

                    Step::make('Features & Media')
                        ->schema([
                            CheckboxList::make('Features')
                                ->relationship('features', 'name')
                                ->columns(4)
                                ->columnSpanFull()
                                ->required(),

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
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
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
            ])->modifyQueryUsing(fn(Builder $query) => $query->where('provider_type','App\Models\User')->where('provider_id', auth()->id()));
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
//    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
//    {
//        return parent::getEloquentQuery()->where('provider_type','App\Models\User')->where('provider_id', auth()->id());
//    }
}

