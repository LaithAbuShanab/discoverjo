<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopTenResource\Pages;
use App\Models\TopTen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class TopTenResource extends Resource
{
    use Translatable;

    protected static ?string $model = TopTen::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Discover Jordan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Place Details')
                    ->description('Provide details about the place and its rank.')
                    ->schema([
                        Grid::make(2) // Using a two-column grid for better alignment.
                            ->schema([
                                Forms\Components\Select::make('place_id')
                                    ->label('Place') // Adding a label for clarity.
                                    ->preload(true)
                                    ->searchable()
                                    ->relationship(
                                        'place',
                                        'name',
                                        modifyQueryUsing: fn(Builder $query) => $query->whereDoesntHave('popularPlaces')
                                    )
                                    ->disableOptionWhen(fn(string $value): bool => in_array($value, TopTen::get()->pluck('place_id')->toArray()))
                                    ->required()
                                    ->hint('Select a place from the list.'), // Adding a hint for better UX.

                                Forms\Components\TextInput::make('rank')
                                    ->label('Rank') // Adding a label for clarity.
                                    ->required()
                                    ->placeholder('Please Enter Rank')
                                    ->numeric()
                                    ->unique()
                                    ->hint('Enter the rank for the selected place.'), // Adding a hint for better UX.
                            ]),
                    ])
                    ->columns(1), // One column for the section layout.
            ])
            ->columns(1); // Main form is a single-column layout.
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
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
            'index' => Pages\ListTopTens::route('/'),
            'create' => Pages\CreateTopTen::route('/create'),
            'edit' => Pages\EditTopTen::route('/{record}/edit'),
        ];
    }
}
