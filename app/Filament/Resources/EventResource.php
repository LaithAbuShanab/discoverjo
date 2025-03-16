<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Concerns\Translatable;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Illuminate\Support\Str;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class EventResource extends Resource
{

    protected static ?string $model = Event::class;

    protected static ?string $navigationGroup = 'Event & Volunteering';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

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
                    ->description('Provide the basic information about the event.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Event Name')
                                    ->placeholder('Please Enter Event Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->translatable(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->placeholder('Please Enter Slug')
                                    ->maxLength(255),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('description')->placeholder('Please Enter Description')->required()->columnSpanFull()->translatable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('address')->required()->placeholder('Please Enter Address')->translatable(),
                                Forms\Components\Select::make('region_id')->relationship('region', 'name')->required(),
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
                                Forms\Components\TextInput::make('price')->placeholder('Please Enter Price')->required()->numeric(),
                                Forms\Components\TextInput::make('attendance_number')->placeholder('Please Enter Attendance Number')->required()->numeric(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')->required()->inline(false),
                                Forms\Components\TextInput::make('link')->placeholder('Please Enter Link')->required()->url(),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('organizer_id')
                                    ->relationship('organizers', 'name')
                                    ->required()
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),
                            ])

                    ])
                    ->columns(1),

                Section::make('Additional Details')
                    ->description('Set the priority and upload an image for the event.')
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                SpatieMediaLibraryFileUpload::make('image')
                                    ->label('Event Image')
                                    ->collection('event')
                                    ->conversion('event_website')
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
                SpatieMediaLibraryImageColumn::make('image')->collection('event')->label('Image')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('region.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_datetime')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('end_datetime')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('status'),
                Tables\Columns\TextColumn::make('price')->money('JOD')->sortable(),
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
                ActivityLogTimelineTableAction::make('Activities'),
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
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
