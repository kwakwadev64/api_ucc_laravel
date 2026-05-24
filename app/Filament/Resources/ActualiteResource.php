<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActualiteResource\Pages;
use App\Filament\Resources\ActualiteResource\RelationManagers;
use App\Models\Actualite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextArea;

class ActualiteResource extends Resource
{
    protected static ?string $model = Actualite::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('titre')->label('Titre de l\'actualité')->required(),
            TextArea::make('description')->label('Description')->required()->maxLength(3000),
            TextInput::make('location')->label('Lieu / Campus'),
            Select::make('filter_type')
                ->label('Filtre cible')
                ->options([
                    'Générale' => 'Générale',
                    'Faculté' => 'Faculté ',
                ])->required(),
            TextInput::make('rating')->label('Note / Importance')->numeric()->default(5.0),
            Toggle::make('is_published')->label('Publié')->default(true),
            FileUpload::make('image_url')->image()->directory('actualites')->required()->label('Image d\'illustration'),
        ]);
    }

    

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image_url')->label('Image'),
            TextColumn::make('titre')->label('Titre')->searchable(),
            TextColumn::make('description')->label('Description')->limit(70),
            TextColumn::make('location')->label('Lieu'),
            TextColumn::make('filter_type')->label('Filtre'),
            IconColumn::make('is_published')->boolean()->label('En ligne'),
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
            'index' => Pages\ListActualites::route('/'),
            'create' => Pages\CreateActualite::route('/create'),
            'edit' => Pages\EditActualite::route('/{record}/edit'),
        ];
    }
}
