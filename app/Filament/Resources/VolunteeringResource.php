<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolunteeringResource\Pages;
use App\Filament\Resources\VolunteeringResource\RelationManagers;
use App\Models\Volunteering;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class VolunteeringResource extends Resource
{
    use Translatable;
    protected static ?string $model = Volunteering::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Event & Volunteering';
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
                    ->description('Provide the basic information about the volunteering.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Volunteering Name')
                                    ->unique()
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state, $livewire) {
                                        if ($livewire->activeLocale === 'en') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->maxLength(255),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('description')->required()->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('address')
                                    ->required(),
                                Forms\Components\Select::make('region_id')
                                    ->relationship('region', 'name')
                                    ->required()
                                    ->getOptionLabelFromRecordUsing(function ($record, $livewire) {
                                        return $record->getTranslation('name', $livewire->activeLocale);
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_datetime')
                                    ->required()
                                    ->minDate(now())
                                    ->reactive() // Make it reactive so changes can trigger updates
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Ensure end_datetime is not before start_datetime
                                        $set('end_datetime', null);
                                    }),
                                Forms\Components\DateTimePicker::make('end_datetime')
                                    ->required()
                                    ->minDate(fn (callable $get) => $get('start_datetime')) // Ensure it starts after start_datetime
                                    ->rules(['after:start_datetime']),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('hours_worked')->required()->numeric(),
                                Forms\Components\TextInput::make('attendance_number')->required()->numeric(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')->required()->inline(false),
                                Forms\Components\TextInput::make('link')->required()->url(),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('organizer_id')
                                    ->relationship('organizers', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record, $livewire) => $record->getTranslation('name', $livewire->activeLocale))
                                    ->required()
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),
                            ])

                    ])
                    ->columns(1),

                Section::make('Additional Details')
                    ->description('Set the priority and upload an image for the volunteering.')
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                SpatieMediaLibraryFileUpload::make('image')
                                    ->label('volunteering Image')
                                    ->collection('volunteering')
                                    ->conversion('volunteering_website')
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
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                SpatieMediaLibraryImageColumn::make('image')->collection('volunteering')->label('Image')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('region.name')->searchable()->sortable()->getStateUsing(fn($record, $livewire) => $record->region?->getTranslation('name', $livewire->activeLocale)),
                Tables\Columns\TextColumn::make('start_datetime')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('end_datetime')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('status'),
                Tables\Columns\TextColumn::make('hours_worked')->sortable(),
                Tables\Columns\TextColumn::make('attendance_number')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolunteerings::route('/'),
            'create' => Pages\CreateVolunteering::route('/create'),
            'edit' => Pages\EditVolunteering::route('/{record}/edit'),
        ];
    }
}
