<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestionPlaceResource\Pages;
use App\Filament\Resources\SuggestionPlaceResource\RelationManagers;
use App\Models\SuggestionPlace;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuggestionPlaceResource extends Resource
{
    protected static ?string $model = SuggestionPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('place_name')
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->required(),
                SpatieMediaLibraryFileUpload::make('suggestion_place')
                    ->collection('suggestion_place_app')
                    ->columnSpanFull()
                    ->multiple()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\ToggleColumn::make('status')
                    ->sortable(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('Media')->allCollections()->circular()->stacked()->limit(3)->limitedRemainingText(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSuggestionPlaces::route('/'),
            'view' => Pages\ViewSuggestionPlace::route('/{record}'),

        ];
    }
}
