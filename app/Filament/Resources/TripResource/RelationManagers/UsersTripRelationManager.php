<?php

namespace App\Filament\Resources\TripResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersTripRelationManager extends RelationManager
{
    protected static string $relationship = 'usersTrip';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\select::make('user')
                    ->relationship('user','username')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('user.username'),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            0 => 'Inactive',
                            1 => 'Active',
                            2 => 'Cancelled by Creator',
                            3 => 'Cancelled by User',
                            4 => 'Cancelled by Admin',
                            default => 'Unknown',
                        };
                    })

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
