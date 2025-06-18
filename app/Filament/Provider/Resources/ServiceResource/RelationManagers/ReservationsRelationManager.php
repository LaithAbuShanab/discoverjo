<?php

namespace App\Filament\Provider\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('panel.provider.reservations');
    }

    public static function getModelLabel(): string
    {
        return __('panel.provider.reservation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.provider.reservations');
    }

    public function form(Form $form): Form
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
                    ->columns(1) // Optional: controls layout within the section
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->label(__('panel.provider.id'))->sortable(),
                TextColumn::make('user.username')->label(__('panel.provider.username'))->searchable(),
                TextColumn::make('date')->label(__('panel.provider.date'))->sortable(),
                TextColumn::make('start_time')->label(__('panel.provider.start-time'))->sortable(),
                TextColumn::make('contact_info')->label(__('panel.provider.contact-information'))->searchable(),
                TextColumn::make('total_price')->label(__('panel.provider.total-price'))->money('JOD'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('panel.guide.status'))
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
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
