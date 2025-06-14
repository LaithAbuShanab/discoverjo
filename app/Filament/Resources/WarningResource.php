<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarningResource\Pages;
use App\Filament\Resources\WarningResource\RelationManagers;
use App\Models\Warning;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\BadgeColumn;


class WarningResource extends Resource
{
    protected static ?string $model = Warning::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reporter_id')
                    ->relationship('reporter', 'username')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('reported_id')
                    ->relationship('reported', 'username')
                    ->required()
                    ->disabled(),
                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        0 => 'Pending',
                        1 => 'Reviewed',
                        2 => 'Dismissed',
                    ])
                    ->default(0)
                    ->required()
                    ->label('Status'),
                Grid::make(2)->schema([
                    SpatieMediaLibraryFileUpload::make('warning_app')
                        ->collection('warning_app')
                        ->multiple()
                        ->columnSpanFull()
                        ->panelLayout('grid'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter.username')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reported.username')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => 'Pending',
                        1 => 'Reviewed',
                        2 => 'Dismissed',
                        default => 'Unknown',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        0 => 'gray',
                        1 => 'success',
                        2 => 'danger',
                        default => 'secondary',
                    }),
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
            'index' => Pages\ListWarnings::route('/'),
//            'create' => Pages\CreateWarning::route('/create'),
            'edit' => Pages\EditWarning::route('/{record}/edit'),
            'view'=>Pages\ViewWarning::route('/{record}/view'),
        ];
    }
}
