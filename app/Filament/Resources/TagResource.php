<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class TagResource extends Resource
{

    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'App sections';

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
                Section::make('Basic Information')
                    ->description('Provide the basic details for the tag.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tag Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->translatable(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Section::make('Tag Images')
                    ->description('Upload images for the active and inactive states of the tag.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('tag_active_image')
                                    ->label('Tag Active Image')
                                    ->collection('tag_active')
                                    ->conversion('tag_active_app')
                                    ->required(),

                                SpatieMediaLibraryFileUpload::make('tag_inactive_image')
                                    ->label('Tag Inactive Image')
                                    ->collection('tag_inactive')
                                    ->conversion('tag_inactive_app')
                                    ->required(),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
