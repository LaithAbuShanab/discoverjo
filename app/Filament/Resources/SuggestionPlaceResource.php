<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestionPlaceResource\Pages;
use App\Models\SuggestionPlace;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class SuggestionPlaceResource extends Resource
{
    protected static ?string $model = SuggestionPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Suggestion & Contact';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Place Information
                Section::make('Place Information')
                    ->description('Provide the details of the place.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('place_name')
                                    ->label('Place Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('address')
                                    ->label('Address')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columns(1),

                // Section 2: Additional Options
                Section::make('Additional Options')
                    ->description('Set the status and upload images.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Active Status')
                                    ->required(),

                                SpatieMediaLibraryFileUpload::make('suggestion_place')
                                    ->label('Place Images')
                                    ->collection('suggestion_place')
                                    ->multiple()
                                    ->required()
                                    ->columnSpanFull()
                                    ->panelLayout('grid')
                            ]),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
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
            ->defaultSort('id', 'desc')
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
