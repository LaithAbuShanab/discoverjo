<?php

namespace App\Filament\Host\Resources\PropertyResource\RelationManagers;

use App\Models\User;
use App\Notifications\Users\Host\ChangeStatusReservationNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Lang;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('panel.host.reservations');
    }

    public static function getModelLabel(): string
    {
        return __('panel.host.reservation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.host.reservations');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('panel.host.reservation-info'))
                    ->description(__('panel.host.reservation-info-desc'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label(__('panel.host.id'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Select::make('user_id')
                                    ->label(__('panel.host.username'))
                                    ->relationship('user', 'username')
                                    ->disabled()
                                    ->dehydrated(false),

                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\ToggleButtons::make('status')
                                    ->label(__('panel.host.status'))
                                    ->inline()
                                    ->options([
                                        0 => __('panel.host.pending'),
                                        1 => __('panel.host.confirmed'),
                                        2 => __('panel.host.cancelled'),
                                        3 => __('panel.host.completed'),
                                    ])
                                    ->colors([
                                        0 => 'primary',
                                        1 => 'info',
                                        2 => 'danger',
                                        3 => 'success',
                                    ])
                                    ->disableOptionWhen(fn(string $value): bool => in_array((int) $value, [0, 3]))
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('check_in')
                                    ->label(__('panel.host.check-in'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\DatePicker::make('check_out')
                                    ->label(__('panel.host.check-out'))
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
                                    ->label(__('panel.host.total-price'))
                                    ->prefix('JOD')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columns(1) // Optional: controls layout within the section
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->label(__('panel.host.id'))->sortable(),
                Tables\Columns\TextColumn::make('user.username')->label(__('panel.host.username'))->sortable(),
                Tables\Columns\TextColumn::make('check_in')->label(__('panel.host.check-in')),
                Tables\Columns\TextColumn::make('check_out')->label(__('panel.host.check-out')),
                Tables\Columns\TextColumn::make('total_price')->label(__('panel.host.total-price'))->money('JOD'),
                Tables\Columns\TextColumn::make('period.type')->label(__('panel.host.period-type'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => __('panel.host.morning'),
                        2 => __('panel.host.evening'),
                        3 => __('panel.host.overnight'),
                        default => __('panel.host.unknown'),
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'primary',
                        2 => 'info',
                        3 => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('status')->label(__('panel.host.status'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => __('panel.host.pending'),
                        1 => __('panel.host.confirmed'),
                        2 => __('panel.host.cancelled'),
                        3 => __('panel.host.completed'),
                        default => __('panel.host.unknown'),
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $newStatus = (int) $data['status'];

                        if ($newStatus !== $record->status && in_array($newStatus, [1, 2])) {
                            $user = User::find($record->user_id);
                            $property = $record->property;

                            $record->status = $newStatus;
                            $record->save();

                            DatabaseNotification::where('type', 'App\Notifications\Users\Host\ChangeStatusReservationNotification')
                                ->whereJsonContains('data->options->reservation_id', $record->id)
                                ->where('notifiable_id', $user->id)
                                ->delete();

                            $userLang = $user->lang;

                            $statusLabel = $newStatus === 1
                                ? Lang::get('app.notifications.status-confirmed', [], $userLang)
                                : Lang::get('app.notifications.status-cancelled', [], $userLang);

                            $notificationData = [
                                'notification' => [
                                    'title' => Lang::get('app.notifications.reservation-status-updated-title', [], $userLang),
                                    'body'  => Lang::get('app.notifications.reservation-status-updated-body', [
                                        'reservation_id' => $record->id,
                                        'status'         => $statusLabel,
                                    ], $userLang),
                                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                                    'sound' => 'default',
                                ],
                                'data' => [
                                    'type'           => 'property_reservation',
                                    'slug'           => $property->slug,
                                    'property_id'    => $property->id,
                                    'reservation_id' => $record->id,
                                    'new_status'     => $newStatus === 1 ? 'confirmed' : 'cancelled',
                                ],
                            ];

                            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
                            if (!empty($tokens)) {
                                sendNotification($tokens, $notificationData);
                            }

                            Notification::send($user, new ChangeStatusReservationNotification($record));
                        }

                        return $record;
                    })
                    ->successNotificationTitle(__('panel.host.status-updated'))
            ]);
    }
}
