<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepliesResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class RepliesResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Monitoring Department';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('parent_id')->count();
    }

    public static function getLabel(): string
    {
        return 'Replies';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: User and Post Information
                Section::make('User & Post Information')
                    ->description('Provide the user and post details.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user', 'username')
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('post_id')
                                    ->label('Post Type')
                                    ->relationship('post', 'visitable_type')
                                    ->searchable()
                                    ->required(),
                            ]),

                    ])
                    ->columns(1),

                // Section 2: Comment Content
                Section::make('Reply Content')
                    ->description('Enter the reply content.')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull()
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.username')->searchable(),
                Tables\Columns\TextColumn::make('post.content')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('content')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->where('parent_id', '!=', null));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReplies::route('/'),
            'view' => Pages\ViewReplies::route('/{record}'),
        ];
    }
}
