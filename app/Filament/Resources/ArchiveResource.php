<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArchiveResource extends Resource
{
    protected static ?string $model = Archive::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Archives';

    protected static ?string $modelLabel = 'Archive';

    protected static ?string $pluralModelLabel = 'Archives';

    protected static ?string $navigationGroup = 'Gestion académique';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom du cours')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion')
                    ->relationship(
                        'promotion',
                        'name'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('semester')
                    ->label('Semestre')
                    ->options([
                        'S1' => 'Semestre 1',
                        'S2' => 'Semestre 2',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('year')
                    ->label('Année')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label('Type d’archive')
                    ->options([
                        'exam' => 'Anciens examens',
                        'td_tp' => 'TD / TP',
                        'interro' => 'Interrogations',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Fichier')
                    ->disk('public')
                    ->directory('archives')
                    ->required()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->preserveFilenames(false)
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Cours')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semestre')
                    ->badge(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Année')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'exam' => 'Anciens examens',
                            'td_tp' => 'TD / TP',
                            'interro' => 'Interrogations',
                            default => $state,
                        }
                    )
                    ->badge(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Format')
                    ->badge(),

                Tables\Columns\TextColumn::make('uploader.first_name')
                    ->label('Ajouté par')
                    ->formatStateUsing(
                        fn ($state, Archive $record) => trim(
                            ($record->uploader?->first_name ?? '')
                            . ' ' .
                            ($record->uploader?->last_name ?? '')
                        )
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->relationship(
                        'promotion',
                        'name'
                    ),

                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semestre')
                    ->options([
                        'S1' => 'Semestre 1',
                        'S2' => 'Semestre 2',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'exam' => 'Anciens examens',
                        'td_tp' => 'TD / TP',
                        'interro' => 'Interrogations',
                    ]),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort(
                'created_at',
                'desc'
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArchives::route('/'),

            'exams' => Pages\ListExamArchives::route('/examens'),

            'td-tp' => Pages\ListTdTpArchives::route('/td-tp'),

            'interro' => Pages\ListInterroArchives::route('/interrogations'),

            'create' => Pages\CreateArchive::route('/create'),

            'edit' => Pages\EditArchive::route('/{record}/edit'),
        ];
    }
}
