<?php

namespace App\Filament\Resources\AmenityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->translatable(),
                Forms\Components\TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->rule(function ($get, $record) {
                        return Rule::unique('amenities', 'priority')
                            ->where(function ($query) use ($get, $record) {
                                $parentId = $get('parent_id') ?? null;

                                $query->where('parent_id', $parentId);
                            })
                            ->ignore($record?->id);
                    }),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', function ($query) {
                        $query->whereNull('parent_id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('amenity')
                    ->collection('amenity')
                    ->conversion('amenity_app')
                    ->label('Icon of Amenity'),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('parent_id', '!=', null)->orderByDesc('priority'))
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
