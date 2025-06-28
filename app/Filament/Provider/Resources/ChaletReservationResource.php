<?php

namespace App\Filament\Provider\Resources;

use App\Filament\Provider\Resources\ChaletReservationResource\{Pages, RelationManagers};
use App\Models\{PropertyReservation, User};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Grid as InfoGrid, Section as InfoSection, TextEntry};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChaletReservationResource extends Resource
{
    protected static ?string $model = PropertyReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Chalet Section';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = User::find(Auth::guard('provider')->id());
        if ($user->userTypes()->where('type', 4)->exists()) {
            return true;
        }
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.host.reservations');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.host.reservations');
    }

    public static function getModelLabel(): string
    {
        return __('panel.host.reservation');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('property', function (Builder $query) {
            $query->where('host_id', auth()->id());
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
                                    ->label(__('panel.host.contact-information'))
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

    public static function table(Table $table): Table
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn(Builder $query): Builder => $query->whereHas('property', fn($query) => $query->where('host_id', auth()->id())));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('panel.host.reservation-info'))
                    ->description(__('panel.host.reservation-info-desc'))
                    ->schema([
                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('panel.host.id')),

                                TextEntry::make('user.username')
                                    ->label(__('panel.host.username')),

                                TextEntry::make('period.type')
                                    ->label(__('panel.host.period-type'))
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        1 => 'primary',
                                        2 => 'info',
                                        3 => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        1 => __('panel.host.morning'),
                                        2 => __('panel.host.evening'),
                                        3 => __('panel.host.overnight'),
                                        default => __('panel.host.unknown'),
                                    }),

                                TextEntry::make('status')
                                    ->label(__('panel.host.status'))
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        0 => 'primary',
                                        1 => 'info',
                                        2 => 'danger',
                                        3 => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        0 => __('panel.host.pending'),
                                        1 => __('panel.host.confirmed'),
                                        2 => __('panel.host.cancelled'),
                                        3 => __('panel.host.completed'),
                                        default => __('panel.host.unknown'),
                                    }),
                            ]),

                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('check_in')
                                    ->label(__('panel.host.check-in'))
                                    ->date(),

                                TextEntry::make('check_out')
                                    ->label(__('panel.host.check-out'))
                                    ->date(),

                                TextEntry::make('contact_info')
                                    ->label(__('panel.host.contact-information')),

                                TextEntry::make('total_price')
                                    ->label(__('panel.host.total-price'))
                                    ->money('JOD', true),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
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
            'index' => Pages\ListChaletReservations::route('/'),
            'view'  => Pages\ViewChaletReservation::route('/{record}'),
            'edit'  => Pages\EditChaletReservation::route('/{record}/edit'),
        ];
    }
}
