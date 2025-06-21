<?php

namespace App\Filament\Provider\Resources;

use App\Filament\Provider\Resources\ServiceReservationResource\Pages;
use Filament\Forms\Components\{CheckboxList, DatePicker, Grid, Repeater, Section, Select, SpatieMediaLibraryFileUpload, Textarea, TextInput, TimePicker, Toggle, Wizard, Wizard\Step};
use App\Models\ServiceReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ServiceReservationResource extends Resource
{
    protected static ?string $model = ServiceReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Services Section';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('panel.provider.reservations');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.provider.reservations');
    }

    public static function getModelLabel(): string
    {
        return __('panel.provider.reservation');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('service', function (Builder $query) {
            $query->where('provider_type', 'App\Models\User')->where('provider_id', auth()->id());
        })->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('panel.provider.reservation-info'))
                    ->description(__('panel.provider.reservation-info-desc'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label(__('panel.provider.id'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Select::make('user_id')
                                    ->label(__('panel.provider.username'))
                                    ->relationship('user', 'username')
                                    ->disabled()
                                    ->dehydrated(false),

                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\ToggleButtons::make('status')
                                    ->label(__('panel.provider.status'))
                                    ->inline()
                                    ->options([
                                        0 => __('panel.provider.pending'),
                                        1 => __('panel.provider.confirmed'),
                                        2 => __('panel.provider.cancelled'),
                                        3 => __('panel.provider.completed'),
                                    ])
                                    ->colors([
                                        0 => 'primary',
                                        1 => 'info',
                                        2 => 'danger',
                                        3 => 'success',
                                    ]),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->label(__('panel.provider.date'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TimePicker::make('start_time')
                                    ->label(__('panel.provider.start-time'))
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('contact_info')
                                    ->label(__('panel.provider.contact-information'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('total_price')
                                    ->label(__('panel.provider.total-price'))
                                    ->prefix('JOD')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
                Forms\Components\Section::make(__('panel.provider.reservation-details'))
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->label(__('panel.provider.reservation-details'))
                            ->relationship('details')
                            ->disabled()
                            ->dehydrated(false)
                            ->columns(3)
                            ->schema([
                                Forms\Components\Select::make('reservation_detail')
                                    ->label(__('panel.provider.age-groups'))
                                    ->options([
                                        1 => __('panel.provider.adult'),
                                        2 => __('panel.provider.child'),
                                    ])
                                    ->disabled(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label(__('panel.provider.quantity'))
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\Select::make('price_age_id')
                                    ->label(__('panel.provider.price-age'))
                                    ->relationship('priceAge', 'price')
                                    ->disabled(),

                                Forms\Components\TextInput::make('price_per_unit')
                                    ->label(__('panel.provider.price-per-unit'))
                                    ->numeric()
                                    ->prefix('JOD')
                                    ->disabled(),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label(__('panel.provider.total-price'))
                                    ->numeric()
                                    ->prefix('JOD')
                                    ->disabled(),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1)
                    ->description(__('panel.provider.reservation-details-desc')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->label(__('panel.provider.username'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label(__('panel.provider.service'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('panel.provider.date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label(__('panel.provider.start-time'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_info')
                    ->label(__('panel.provider.contact-information'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('panel.provider.status'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => __('panel.provider.pending'),
                        1 => __('panel.provider.confirmed'),
                        2 => __('panel.provider.cancelled'),
                        3 => __('panel.provider.completed'),
                        default => __('panel.provider.unknown'),
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        0 => 'primary',
                        1 => 'info',
                        2 => 'danger',
                        3 => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('panel.provider.total-price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('panel.provider.created-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('panel.provider.updated-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn(Builder $query) => $query->whereHas('service', fn(Builder $query) => $query->where('provider_type', 'App\Models\User')->where('provider_id', auth()->id())));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('panel.provider.reservation-info'))
                    ->description(__('panel.provider.reservation-info-desc'))
                    ->schema([
                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('panel.provider.id')),

                                TextEntry::make('user.username')
                                    ->label(__('panel.provider.username')),

                                TextEntry::make('status')
                                    ->label(__('panel.provider.status'))
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        0 => 'primary',
                                        1 => 'info',
                                        2 => 'danger',
                                        3 => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        0 => __('panel.provider.pending'),
                                        1 => __('panel.provider.confirmed'),
                                        2 => __('panel.provider.cancelled'),
                                        3 => __('panel.provider.completed'),
                                        default => __('panel.provider.unknown'),
                                    }),
                            ]),

                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('date')
                                    ->label(__('panel.provider.date'))
                                    ->date(),

                                TextEntry::make('start_time')
                                    ->label(__('panel.provider.start-time'))
                                    ->time(),

                                TextEntry::make('contact_info')
                                    ->label(__('panel.provider.contact-information')),

                                TextEntry::make('total_price')
                                    ->label(__('panel.provider.total-price'))
                                    ->money('JOD', true),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                InfoSection::make(__('panel.provider.reservation-details'))
                    ->description(__('panel.provider.reservation-details-desc'))
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label(__('panel.provider.reservation-details'))
                            ->schema([
                                InfoGrid::make(3)
                                    ->schema([
                                        TextEntry::make('reservation_detail')
                                            ->label(__('panel.provider.age-groups'))
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                1 => __('panel.provider.adult'),
                                                2 => __('panel.provider.child'),
                                                default => __('panel.provider.unknown'),
                                            })
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                1 => 'info',
                                                2 => 'warning',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('quantity')
                                            ->label(__('panel.provider.quantity')),

                                        TextEntry::make('priceAge.price')
                                            ->label(__('panel.provider.price-age'))
                                            ->money('JOD', true)
                                            ->badge()
                                            ->color(fn($record) => $record->priceAge?->price == $record->price_per_unit ? 'success' : 'gray'),

                                        TextEntry::make('price_per_unit')
                                            ->label(__('panel.provider.price-per-unit'))
                                            ->money('JOD', true),

                                        TextEntry::make('subtotal')
                                            ->label(__('panel.provider.total-price'))
                                            ->money('JOD', true),
                                    ]),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceReservations::route('/'),
            // 'create' => Pages\CreateServiceReservation::route('/create'),
            'view' => Pages\ViewServiceReservation::route('/{record}'),
            'edit' => Pages\EditServiceReservation::route('/{record}/edit'),
        ];
    }
}
