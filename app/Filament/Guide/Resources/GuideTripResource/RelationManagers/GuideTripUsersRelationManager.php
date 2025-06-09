<?php

namespace App\Filament\Guide\Resources\GuideTripResource\RelationManagers;

use App\Models\GuideTripUser;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GuideTripUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'guideTripUsers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Forms\Components\TextInput::make('age')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?GuideTripUser $record) => $record && $record->user_id !== Auth::id()),

                Section::make('Trip Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                0 => 'Pending',
                                1 => 'Active',
                                2 => 'Cancelled',
                            ]), // Only field that is editable
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
//            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('age'),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        0 => 'Pending',
                        1 => 'Active',
                        2 => 'Cancelled',
                        default => 'Unknown',
                    })
                    ->badge() // Optional: makes it look like a colored badge
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
