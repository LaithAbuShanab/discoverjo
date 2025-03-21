<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewableResource\Pages;
use App\Models\Reviewable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class ReviewableResource extends Resource
{
    protected static ?string $model = Reviewable::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Monitoring Department';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Review Information')
                    ->description('Fill in the review details below.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User name')
                                    ->relationship('user', 'username')
                                    ->required(),

                                Forms\Components\TextInput::make('reviewable_type')
                                    ->label('Reviewable Type')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('reviewable_id')
                                    ->label('Reviewable Item')
                                    ->options(function (callable $get) {
                                        $type = $get('reviewable_type');

                                        if (! class_exists($type)) {
                                            return [];
                                        }

                                        return $type::query()
                                            ->pluck('name', 'id') // Ensure 'name' exists in the model
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive(),


                                Forms\Components\TextInput::make('rating')
                                    ->label('Rating')
                                    ->required()
                                    ->numeric(),
                            ]),

                        Forms\Components\Textarea::make('comment')
                            ->label('Comment')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username') // Display the user's username instead of ID
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('reviewable_type')
                    ->label('Reviewable Type')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reviewable.name') // Fetch name from related model
                    ->label('Reviewable Item')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),

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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviewables::route('/'),
            'view' => Pages\ViewReviewable::route('/{record}'),
        ];
    }
}
