<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoFamilleResource\Pages;
use App\Filament\Resources\PhotoFamilleResource\RelationManagers;
use App\Models\PhotoFamille;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class PhotoFamilleResource extends Resource
{
    protected static ?string $model = PhotoFamille::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->label('Légende')->required(),
            TextInput::make('description')->label('Description (Optionnel)'),
            Select::make('faculte')->label('Faculté')->options([
                'Informatique' => 'Informatique',
                'Économie' => 'Économie',
                'Droit' => 'Droit',
                'Médecine' => 'Médecine',
                'Sciences Sociales' => 'Communication Sociales',
            ])->required(),
            Select::make('promotion')->label('Promotion')->options([
                'L1' => 'L1',
                'L2' => 'L2',
                'L3' => 'L3',
                'M1' => 'M1',
                'M2' => 'M2',
            ])->required(),
            Toggle::make('is_active')->label('Afficher sur l\'app')->default(true),
            FileUpload::make('image_path')->image()->directory('famille')->required()->label('Photo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Photo'),
                TextColumn::make('title')->label('Légende')->searchable(),
                TextColumn::make('description')->label('Description')->limit(50),
                TextColumn::make('faculte')->label('Faculté')->searchable(),
                TextColumn::make('promotion')->label('Promotion')->searchable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
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
            'index' => Pages\ListPhotoFamilles::route('/'),
            'create' => Pages\CreatePhotoFamille::route('/create'),
            'edit' => Pages\EditPhotoFamille::route('/{record}/edit'),
        ];
    }
}
