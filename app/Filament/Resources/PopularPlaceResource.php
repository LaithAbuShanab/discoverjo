<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopularPlaceResource\Pages;
use App\Models\PopularPlace;
use Filament\Forms\Components\{Section, Grid, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class PopularPlaceResource extends Resource
{
    protected static ?string $model = PopularPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Discover Jordan';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Place Pricing Details')
                    ->description('Provide details about the place and its pricing.')
                    ->schema([
                        Grid::make(2) // Two-column layout
                            ->schema([
                                Select::make('place_id')
                                    ->label('Place')
//                                    ->preload(true)
                                    ->searchable()
                                    ->relationship(
                                        'place',
                                        'name',
                                        modifyQueryUsing: fn(Builder $query) => $query->whereDoesntHave('topTenPlaces')
                                    )
                                    ->disableOptionWhen(fn(string $value): bool => in_array($value, PopularPlace::pluck('place_id')->toArray()))
                                    ->required()
                                    ->hint('Select a place that is not in the top ten.'),

                                TextInput::make('local_price')
                                    ->label('Local Price')
                                    ->numeric()
                                    ->placeholder('Please Enter Local Price')
                                    ->required()
                                    ->prefix('JD')
                                    ->hint('Set the price for local visitors.'),

                                TextInput::make('foreign_price')
                                    ->label('Foreign Price')
                                    ->numeric()
                                    ->placeholder('Please Enter Foreign Price')
                                    ->required()
                                    ->prefix('JD')
                                    ->hint('Set the price for foreign visitors.'),
                            ]),
                    ])
                    ->columns(1), // Single-column section layout
            ])
            ->columns(1); // Main form is a single-column layout
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place.id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('place.name')->searchable(),
                Tables\Columns\TextColumn::make('place.phone_number')->searchable(),
                RatingColumn::make('place.rating')->theme(RatingTheme::HalfStars)->sortable()->color('warning')->default(0.0),
                Tables\Columns\TextColumn::make('place.region.name')->searchable()->sortable(),
                SpatieMediaLibraryImageColumn::make('place.Media')->allCollections()->circular()->stacked(),
            ])
            ->filters([
                //
            ])
            ->actions(ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ActivityLogTimelineTableAction::make('Activities'),
            ]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPopularPlaces::route('/'),
            'create' => Pages\CreatePopularPlace::route('/create'),
            'edit' => Pages\EditPopularPlace::route('/{record}/edit'),
        ];
    }
}
