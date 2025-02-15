<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactUsResource\Pages;
use App\Models\ContactUs;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactUsResource extends Resource
{
    protected static ?string $model = ContactUs::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Suggestion & Contact';

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
                // Section 1: Contact Information
                Section::make('Contact Information')
                    ->description('Provide the basic contact details.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columns(1),

                // Section 2: Message Details
                Section::make('Message Details')
                    ->description('Enter the subject and message.')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('subject')
                                    ->label('Subject')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('message')
                                    ->label('Message')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1),

                // Section 3: Additional Options
                Section::make('Additional Options')
                    ->description('Set status and upload attachments.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Active Status')
                                    ->required(),

                                SpatieMediaLibraryFileUpload::make('contact')
                                    ->label('Attachments')
                                    ->collection('contact')
                                    ->columnSpanFull()->multiple()->required()
                                    ->panelLayout('grid')
                            ]),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->sortable(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('Media')->allCollections()->circular()->stacked()->limit(3)->limitedRemainingText(),

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactUs::route('/'),
            'view' => Pages\ViewContactUs::route('/{record}'),
        ];
    }
}
