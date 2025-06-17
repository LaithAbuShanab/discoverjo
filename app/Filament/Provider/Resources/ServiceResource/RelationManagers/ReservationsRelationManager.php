<?php

namespace App\Filament\Provider\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;


class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Start Time')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('contact_info')
                    ->label('Contact Info')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('total_price')
                    ->label('Total Price')
                    ->prefix('JOD')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                        0 => 'pending',
                        1 => 'confirmed',
                        2 => 'cancelled',
                    ])
                    ->native(false),

                Forms\Components\Repeater::make('details')
                    ->label('Reservation Details')
                    ->relationship('details')
                    ->disabled()
                    ->dehydrated(false)
                    ->schema([
                        Forms\Components\Select::make('reservation_detail')
                            ->label('Type')
                            ->options([
                                1 => 'Adult',
                                2 => 'Child',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\Select::make('price_age_id')
                            ->label('Age Pricing')
                            ->relationship('priceAge', 'id')
                            ->disabled(),

                        Forms\Components\TextInput::make('price_per_unit')
                            ->label('Price per Unit')
                            ->disabled(),

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }


        public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.username')->label('User')->searchable(),
                TextColumn::make('date')->sortable(),
                TextColumn::make('start_time')->label('Start Time'),
                TextColumn::make('contact_info'),
                TextColumn::make('total_price')->money('JOD'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('panel.guide.status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        0 => 'pending',
                        1 => 'confirmed',
                        2 => 'cancelled',
                        default => __('panel.status.unknown'),
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        0 => 'gray',
                        1 => 'success',
                        2 => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
