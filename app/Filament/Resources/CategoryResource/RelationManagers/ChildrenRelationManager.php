<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'Children';

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
                        $parentId = $get('parent_id');
                        $ignoreId = $record?->id;

                        return new \App\Rules\UniquePriorityWithinParent($parentId, $ignoreId);
                    }),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', function ($query) {
                        $query->whereNull('parent_id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('category_active_image')
                    ->collection('category_active')
                    ->conversion('category_active_app')
                    ->label('Image Active'),
                SpatieMediaLibraryFileUpload::make('category_inactive_image')
                    ->collection('category_inactive')
                    ->conversion('category_inactive_app')
                    ->label('Image Inactive'),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('category_active')->label('Image')->circular(),
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
