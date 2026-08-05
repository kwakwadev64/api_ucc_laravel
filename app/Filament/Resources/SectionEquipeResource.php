<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionEquipeResource\Pages;
use App\Models\SectionEquipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectionEquipeResource extends Resource
{
    protected static ?string $model = SectionEquipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Sections Équipe';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('section_id')
                    ->label('Type de section')
                    ->options([
                        'faculte' => 'Faculté',
                        'gouvernement' => 'Gouvernement',
                        'cp_cpa' => 'CP / CPA',
                        'developpeurs' => 'Développeurs',
                    ])
                    ->required(),
                    
                Forms\Components\Select::make('membre_id')
                    ->label('Membre rattaché')
                    ->relationship('membre', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('titre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('annee')
                    ->required()
                    ->placeholder('Ex: 2026'),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section_id')
                    ->label('Section')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('membre.nom')
                    ->label('Membre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('annee')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section_id')
                    ->options([
                        'faculte' => 'Faculté',
                        'gouvernement' => 'Gouvernement',
                        'cp_cpa' => 'CP / CPA',
                        'developpeurs' => 'Développeurs',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectionEquipes::route('/'),
            'create' => Pages\CreateSectionEquipe::route('/create'),
            'edit' => Pages\EditSectionEquipe::route('/{record}/edit'),
        ];
    }
}