<?php

namespace App\Filament\Resources\CommentResource\RelationManagers;

use App\Filament\Resources\RepliesResource;
use App\Models\Comment;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.username')->searchable(),
                Tables\Columns\TextColumn::make('post.content')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('content')->searchable()->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->url(fn(Comment $record) => route('filament.admin.resources.replies.view', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
