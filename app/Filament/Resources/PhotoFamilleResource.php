<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoFamilleResource\Pages;
use App\Models\PhotoFamille;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Select;
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

            Select::make('academic_year_id')
                ->label('Année Académique')
                ->relationship('academicYear', 'name')
                ->required()
                ->searchable()
                ->preload(),

            Select::make('faculte')
                ->label('Faculté')
                ->options([
                    'Informatique' => 'Informatique',
                    'Économie' => 'Économie',
                    'Droit' => 'Droit',
                    'Médecine' => 'Médecine',
                    'Sciences Sociales' => 'Communication Sociales',
                ])
                ->required(),

            Select::make('promotion')
                ->label('Promotion')
                ->options([
                    'L1' => 'L1',
                    'L2' => 'L2',
                    'L3' => 'L3',
                    'M1' => 'M1',
                    'M2' => 'M2',
                ])
                ->required(),

            Toggle::make('is_active')
                ->label('Afficher sur l\'app')
                ->default(true),

            FileUpload::make('image_path')
                ->image()
                ->directory('famille')
                ->required()
                ->label('Photo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Photo'),
                TextColumn::make('title')->label('Légende')->searchable(),
                TextColumn::make('academicYear.name')->label('Année Académique')->sortable(),
                TextColumn::make('faculte')->label('Faculté')->searchable(),
                TextColumn::make('promotion')->label('Promotion')->searchable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                SelectFilter::make('academicYear') // Corrigé ici pour correspondre à la relation
                    ->relationship('academicYear', 'name')
                    ->label('Filtrer par Année Académique'),
                SelectFilter::make('faculte')
                    ->label('Faculté')
                    ->options([
                        'Informatique' => 'Informatique',
                        'Économie' => 'Économie',
                        'Droit' => 'Droit',
                        'Médecine' => 'Médecine',
                        'Sciences Sociales' => 'Communication Sociales',
                    ]),
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
            'index' => Pages\ListPhotoFamilles::route('/'),
            'create' => Pages\CreatePhotoFamille::route('/create'),
            'edit' => Pages\EditPhotoFamille::route('/{record}/edit'),
        ];
    }
}