<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class SliderResource extends Resource
{

    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

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
                    ->description('Provide the basic information about the slider.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Slider title')
                                    ->required()
                                    ->maxLength(255)
                                    ->translatable(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->maxLength(255),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('content')->required()->columnSpanFull()->translatable(),
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'onboarding' => 'Onboarding',
                                        'banner' => 'Banner',
                                    ])
                                    ->label('Type')
                                    ->rules(['required', Rule::in(['onboarding', 'banner'])]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')->required()->inline(false),
                                Forms\Components\TextInput::make('priority')
                                    ->required()
                                    ->numeric()
                                    ->rules([
                                        Rule::unique('sliders', 'priority')
                                            ->where(fn ($query) =>
                                            $query->where('type', request()->input('type')) // Ensure unique priority per type
                                            ),
                                    ])
                                    ->label('Priority'),
                            ]),

                    ])
                    ->columns(1),

                Section::make('Additional Details')
                    ->description('Set the priority and upload an image for the event.')
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                SpatieMediaLibraryFileUpload::make('image')
                                    ->label('Event Image')
                                    ->collection('slider')
                                    ->conversion('slider_app')
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
                SpatieMediaLibraryImageColumn::make('image')->collection('slider')->label('Image')->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status'),

                Tables\Columns\TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
