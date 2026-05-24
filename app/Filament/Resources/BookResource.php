<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Components\TextInput\Money;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('titre')->label('Titre du livre')->required(),
                TextInput::make('auteur')->label('Auteur / Professeur')->required(),
                TextInput::make('faculte')->label('Faculté')->required(),
                TextInput::make('prix')->label('Prix de vente ($)')->numeric()->required(),
                TextInput::make('original_prix')->label('Ancien prix (Optionnel)')->numeric(),
                TextInput::make('annee_publication')->label('Année de publication')->required(),
                FileUpload::make('cover_url')->image()->directory('books')->required()->label('Image de couverture'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_url')->label('Couverture'),
                TextColumn::make('titre')->label('Titre')->searchable(),
                TextColumn::make('auteur')->label('Auteur / Professeur')->searchable(),
                TextColumn::make('faculte')->label('Faculté')->searchable(),
                TextColumn::make('prix')->label('Prix de vente ($)')->money('USD', true),
                TextColumn::make('original_prix')->label('Ancien prix ($)')->money('USD', true),
                TextColumn::make('annee_publication')->label('Année de publication'),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
