<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoFamilleResource\Pages;
use App\Models\PhotoFamille;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PhotoFamilleResource extends Resource
{
    protected static ?string $model = PhotoFamille::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Photos de Famille';

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('title')
                ->label('Légende')
                ->required(),

            TextInput::make('description')
                ->label('Description (Optionnel)'),

            // Année académique
            Select::make('academic_year_id')
                ->label('Année académique')
                ->relationship('academicYear', 'name')
                ->required()
                ->searchable()
                ->preload(),

            // Faculté
            Select::make('faculty_id')
                ->label('Faculté')
                ->relationship('faculty', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('promotion_id', null)),

            // Promotion filtrée selon la faculté choisie
            Select::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

            Toggle::make('is_active')
                ->label('Afficher sur l\'application')
                ->default(true),

            FileUpload::make('image_path')
                ->image()
                ->disk('public')
                ->directory('famille')
                ->required()
                ->label('Photo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('image_path')
                    ->label('Photo'),

                TextColumn::make('title')
                    ->label('Légende')
                    ->searchable(),

                TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable(),

                TextColumn::make('faculty.name')
                    ->label('Faculté')
                    ->searchable(),

                TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([

                SelectFilter::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship('academicYear', 'name'),

                SelectFilter::make('faculty_id')
                    ->label('Faculté')
                    ->relationship('faculty', 'name'),

                SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'name'),

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoFamilles::route('/'),
            'create' => Pages\CreatePhotoFamille::route('/create'),
            'edit' => Pages\EditPhotoFamille::route('/{record}/edit'),
        ];
    }
}
