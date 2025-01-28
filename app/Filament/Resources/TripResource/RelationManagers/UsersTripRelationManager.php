<?php

namespace App\Filament\Resources\TripResource\RelationManagers;

use App\Models\UsersTrip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            2 => 'Deleted by Creator',
                            3 => 'Deleted by Admin',
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
