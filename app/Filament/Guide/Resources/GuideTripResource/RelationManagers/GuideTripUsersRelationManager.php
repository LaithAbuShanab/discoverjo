<?php

namespace App\Filament\Guide\Resources\GuideTripResource\RelationManagers;

use App\Models\GuideTripUser;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GuideTripUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'guideTripUsers';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('panel.guide.trips-users');
    }

    public static function getModelLabel(): string
    {
        return __('panel.guide.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.guide.users');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label(__('panel.guide.first-name'))
                    ->placeholder(__('panel.guide.enter-first-name'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn(?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('last_name')
                    ->label(__('panel.guide.last-name'))
                    ->placeholder(__('panel.guide.enter-last-name'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn(?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('phone_number')
                    ->label(__('panel.guide.phone-number'))
                    ->placeholder(__('panel.guide.enter-phone-number'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn(?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('age')
                    ->label(__('panel.guide.age'))
                    ->placeholder(__('panel.guide.enter-age'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn(?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Section::make(__('panel.guide.trip-status'))
                    ->schema([
                        Forms\Components\ToggleButtons::make('status')
                            ->label(__('panel.guide.status'))
                            ->inline()
                            ->options([
                                0 => __('panel.guide.pending'),
                                1 => __('panel.guide.active'),
                                2 => __('panel.guide.cancelled'),
                            ])
                            ->colors([
                                0 => 'warning',
                                1 => 'success',
                                2 => 'danger',
                            ])
                    ])
                    ->columnSpanFull(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label(__('panel.guide.first-name')),
                Tables\Columns\TextColumn::make('last_name')->label(__('panel.guide.last-name')),
                Tables\Columns\TextColumn::make('age')->label(__('panel.guide.age')),
                Tables\Columns\TextColumn::make('phone_number')->label(__('panel.guide.phone-number')),
                Tables\Columns\TextColumn::make('status')->label(__('panel.guide.status'))
                    ->label(__('panel.guide.status'))
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => 'Pending',
                        1 => 'Active',
                        2 => 'Cancelled',
                        default => 'Unknown',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
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
