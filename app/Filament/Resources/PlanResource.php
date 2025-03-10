<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Concerns\Translatable;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Filament\Forms\Components\{TextInput, Textarea, Section, Grid, Repeater, Select, TimePicker};
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanResource extends Resource
{
    use Translatable;

    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Plans';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Provide the essential details about this plan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Plan Name')
                                    ->placeholder('Please Enter Plan Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true) // Updates the state only when the field loses focus
                                    ->afterStateUpdated(function (callable $set, $state, $livewire) {
                                        if ($livewire->activeLocale === 'en') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Please Enter Description')
                            ->required()
                            ->columnSpanFull(),
                    ])

                    ->columns(1),

                Section::make('Plan Schedule')
                    ->description('Define days and activities for the plan.')
                    ->schema([
                        Repeater::make('days')
                            ->label('Days')
                            ->relationship('days') // Ensure a 'days' relationship exists in the Plan model
                            ->schema([
                                TextInput::make('day')
                                    ->label('Day Number')
                                    ->placeholder('Please Enter Day Number')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(7),

                                Repeater::make('activities')
                                    ->label('Activities')
                                    ->relationship('activities')
                                    ->schema([
                                        // Activity Name (English & Arabic inside JSON)
                                        TextInput::make('activity_name.en')
                                            ->label('Activity Name (English)')
                                            ->placeholder('Please Enter Activity Name')
                                            ->required(),

                                        TextInput::make('activity_name.ar')
                                            ->label('Activity Name (Arabic)')
                                            ->placeholder('Please Enter Activity Name')
                                            ->required(),

                                        // Place Selection
                                        Select::make('place_id')
                                            ->label('Place')
                                            ->placeholder('Please Select Place')
                                            ->relationship('place', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(2),

                                        TimePicker::make('start_time')
                                            ->label('Start Time')
                                            ->required()
                                            ->rule(function (callable $get) {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $endTime = $get('end_time'); // Fetch the end_time dynamically
                                                    if ($endTime && $value >= $endTime) {
                                                        $fail('The Start Time must be before the End Time.');
                                                    }
                                                };
                                            }),

                                        TimePicker::make('end_time')
                                            ->label('End Time')
                                            ->required()
                                            ->rule(function (callable $get) {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $startTime = $get('start_time'); // Fetch the start_time dynamically
                                                    if ($startTime && $value <= $startTime) {
                                                        $fail('The End Time must be after the Start Time.');
                                                    }
                                                };
                                            }),

                                        // Notes (English & Arabic inside JSON)
                                        TextInput::make('notes.en')
                                            ->label('Notes (English)')
                                            ->placeholder('Please Enter Notes'),

                                        TextInput::make('notes.ar')
                                            ->label('Notes (Arabic)')
                                            ->placeholder('Please Enter Notes'),
                                    ])
                                    ->columns(2)
                            ])
                            ->columns(1),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('creator.email')->sortable(),
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
