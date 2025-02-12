<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'username')
                    ->required(),

                Forms\Components\Select::make('visitable_type')
                    ->label('Type')
                    ->options([
                        'App\Models\Trip' => 'Trip',
                        'App\Models\Place' => 'Place',
                        'App\Models\Event' => 'Event',
                        'App\Models\Volunteering' => 'Volunteering',
                    ])
                    ->searchable()
                    ->reactive() // Reacts when the user selects a type
                    ->required()
                    ->afterStateUpdated(fn (callable $set) => $set('visitable_id', null)), // Reset visitable_id when type changes

                Forms\Components\Select::make('visitable_id')
                    ->label('Visitable Name')
                    ->options(fn (callable $get) =>
                    $get('visitable_type') && class_exists($get('visitable_type'))
                        ? $get('visitable_type')::pluck('name', 'id')->toArray()
                        : []
                    )
                    ->searchable()
                    ->required()
                    ->reactive(), // Updates when visitable_type changes

                Forms\Components\Select::make('privacy')
                    ->label('Privacy')
                    ->options([
                        0 => 'Only me',
                        1 => 'Public',
                        2 => 'Followers',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('seen_status')
                    ->label('Seen Status')
                    ->default(false),

                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('post')
                    ->collection('post_app')
                    ->columnSpanFull()
                    ->multiple()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitable_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => class_basename($state)) // Extracts only the class name
                    ->searchable(),

                Tables\Columns\TextColumn::make('visitable_id')
                    ->label('Type Name')
                    ->formatStateUsing(function ($record) {
                        $model = $record->visitable_type;
                        $id = $record->visitable_id;

                        if (!$model || !$id) {
                            return 'N/A';
                        }

                        $modelClass = '\\' . $model; // Ensure full namespace
                        if (class_exists($modelClass)) {
                            $instance = $modelClass::find($id);
                            return $instance ? $instance->name ?? 'N/A' : 'N/A';
                        }

                        return 'N/A';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('privacy')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            0 => 'Only me',
                            1 => 'Public',
                            2 => 'Followers',
                        };
                    }),
                Tables\Columns\ToggleColumn::make('seen_status')
                    ->sortable(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('post')->allCollections()->circular()->stacked()->limit(3)->limitedRemainingText(),

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
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'view' => Pages\ViewPost::route('/{record}'),
        ];
    }
}
