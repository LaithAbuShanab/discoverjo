<?php

namespace App\Filament\Resources\ServiceCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                        $parentId = $get('parent_id');
                        $ignoreId = $record?->id;

                        return new \App\Rules\UniquePriorityWithinParentService($parentId, $ignoreId);
                    }),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', function ($query) {
                        $query->whereNull('parent_id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('service_category_active')
                    ->collection('service_category_active')
                    ->conversion('service_category_active_app')
                    ->label('Image Active'),
                SpatieMediaLibraryFileUpload::make('service_category_inactive')
                    ->collection('service_category_inactive')
                    ->conversion('service_category_inactive_app')
                    ->label('Image Inactive'),


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
