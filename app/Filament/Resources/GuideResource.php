<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideResource\Pages;
use App\Models\User;
use Filament\Forms\Components\{Section, Grid, TextInput, Select, Textarea, Toggle, DatePicker, DateTimePicker};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class GuideResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'App users';

    protected static ?string $navigationLabel = 'Guides';

    protected static ?string $pluralLabel = 'Guides';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('type', 1)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                Section::make('Basic Information')
                    ->description('Personal details and contact information.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('First Name')
                                    ->maxLength(255),

                                TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->maxLength(255),

                                TextInput::make('username')
                                    ->label('Username')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('phone_number')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(255),

                                DatePicker::make('birthday')
                                    ->label('Date of Birth'),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                // Account Security Section
                Section::make('Account Security')
                    ->description('Manage authentication and verification details.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At'),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                 //Guide Details Section
                Section::make('Guide Details')
                    ->description('Details related to guide status and points.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('type')
                                    ->options([
                                        '1' => 'User',
                                        '2' => 'Guide',
                                        '3' => 'Provider',
                                        '4' => 'Chalet Provider',
                                    ]),

                                TextInput::make('points')
                                    ->label('Points')
                                    ->numeric()
                                    ->required()
                                    ->default(0),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric(),

                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric(),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                // Social & Preferences Section
                Section::make('Social & Preferences')
                    ->description('Social logins and language preferences.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('facebook_id')
                                    ->label('Facebook ID')
                                    ->maxLength(255),

                                TextInput::make('google_id')
                                    ->label('Google ID')
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label('Profile Slug')
                                    ->maxLength(255),

                                Select::make('lang')
                                    ->label('Language')
                                    ->options([
                                        'en' => 'English',
                                        'ar' => 'Arabic',
                                    ])
                                    ->default('ar')
                                    ->required(),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                // Status & Description Section
                Section::make('Status & Description')
                    ->description('Set account status and provide additional details.')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        '4' => 'Inactive',
                                        '1' => 'Active',
                                    ]),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Section::make('Image & Attachment')
                    ->description('Upload an image and attachment for the guide.')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                // Profile Image Upload
                                SpatieMediaLibraryFileUpload::make('avatar') // Ensure this matches the model collection
                                    ->label('Profile Image')
                                    ->collection('avatar') // Matches the collection name in the model
                                    ->conversion('avatar_app') // Ensures correct conversion is used
                                    ->image()
                                    ->imageEditor(),

                                SpatieMediaLibraryFileUpload::make('file')
                                    ->label('Attachment')
                                    ->collection('file')
                                    ->acceptedFileTypes(['application/pdf']) // Restrict to PDFs
                                    ->downloadable(), // Adds a download button
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
                Tables\Columns\TextColumn::make('username')->searchable(),
                Tables\Columns\BooleanColumn::make('status')->getStateUsing(fn($record) => $record->status === 1),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sex')->numeric()->sortable()->label('Gender')->formatStateUsing(fn($state) => $state === 1 ? 'male' : ($state === 2 ? 'female' : null)),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            // ->modifyQueryUsing(fn($query) => $query->where('type', 2));
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
            'index' => Pages\ListGuides::route('/'),
            'create' => Pages\CreateGuide::route('/create'),
            'edit' => Pages\EditGuide::route('/{record}/edit'),
        ];
    }
}
