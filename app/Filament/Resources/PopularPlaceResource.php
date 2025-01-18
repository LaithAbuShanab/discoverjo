<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopularPlaceResource\Pages;
use App\Filament\Resources\PopularPlaceResource\RelationManagers;
use App\Models\PopularPlace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Concerns\Translatable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;

class PopularPlaceResource extends Resource
{
    use Translatable;

    protected static ?string $model = PopularPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\Select::make('place_id')
                    ->preload(true)
                    ->searchable()
                    ->relationship('place', 'name', modifyQueryUsing: fn(Builder $query) => $query->whereDoesntHave('topTenPlaces'))
                    ->disableOptionWhen(fn(string $value): bool => in_array($value, PopularPlace::get()->pluck('place_id')->toArray()))
                    ->required(),
                Forms\Components\TextInput::make('local_price')
                    ->numeric()
                    ->placeholder('Please Enter Local Price')
                    ->required()
                    ->prefix('JD'),
                Forms\Components\TextInput::make('foreign_price')
                    ->numeric()
                    ->placeholder('Please Enter Foreign Price')
                    ->required()
                    ->prefix('JD'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place.name')->searchable()
                    ->getStateUsing(fn($record, $livewire) => $record->place?->getTranslation('name', $livewire->activeLocale)),
                Tables\Columns\TextColumn::make('place.phone_number')->searchable(),
                RatingColumn::make('place.rating')->theme(RatingTheme::HalfStars)->sortable()->color('warning')->default(0.0),
                Tables\Columns\TextColumn::make('place.region.name')->searchable()->sortable()
                    ->getStateUsing(fn($record, $livewire) => $record->place->region?->getTranslation('name', $livewire->activeLocale)),
                SpatieMediaLibraryImageColumn::make('place.Media')->allCollections()->circular()->stacked(),
            ])
            ->filters([
                //
            ])
            ->actions(ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
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
