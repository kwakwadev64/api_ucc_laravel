<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorialResource\Pages;
use App\Models\Tutorial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TutorialResource extends Resource
{
    protected static ?string $model = Tutorial::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationLabel = 'Tutoriels';

    protected static ?string $modelLabel = 'Tutoriel';

    protected static ?string $pluralModelLabel = 'Tutoriels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('youtube_type')
                    ->label('Type')
                    ->options([
                        'video' => 'Vidéo YouTube',
                        'playlist' => 'Playlist YouTube',
                    ])
                    ->default('video')
                    ->required()
                    ->live(),

                Forms\Components\TextInput::make('youtube_url')
                    ->label('URL YouTube')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText(
                        'Collez le lien d’une vidéo ou d’une playlist YouTube.'
                    )
                    ->required()
                    ->url()
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Miniature'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('youtube_video_id')
                    ->label('Vidéo YouTube')
                    ->searchable(),

                Tables\Columns\TextColumn::make('youtube_playlist_id')
                    ->label('Playlist')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutorials::route('/'),
            'create' => Pages\CreateTutorial::route('/create'),
            'edit' => Pages\EditTutorial::route('/{record}/edit'),
        ];
    }
}
