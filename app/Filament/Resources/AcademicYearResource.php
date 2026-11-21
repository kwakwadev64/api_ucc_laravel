<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Années académiques';

    protected static ?string $modelLabel = 'Année académique';

    protected static ?string $pluralModelLabel = 'Années académiques';

    protected static ?string $navigationGroup = 'Gestion académique';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations de l’année')
                ->description('Définissez son libellé, ses dates et son statut.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom de l’année académique')
                        ->placeholder('Ex. 2026–2027')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Date de début')
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Date de fin')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'active' => 'En cours',
                            'closed' => 'Clôturée',
                        ])
                        ->default('active')
                        ->required(),
                ])
                ->columns(['md' => 2]),

            Forms\Components\Section::make('Gestion des recours')
                ->description('Contrôlez la période de soumission des recours pour cette année académique.')
                ->schema([
                    Forms\Components\Toggle::make('recours_is_open')
                        ->label('Ouvrir les soumissions de recours')
                        ->helperText('Lorsque cette option est activée, les étudiants peuvent soumettre leurs recours.')
                        ->default(false)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Année académique')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'En cours' : 'Clôturée')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                Tables\Columns\ToggleColumn::make('recours_is_open')
                    ->label('Recours ouverts')
                    ->onColor('success')
                    ->offColor('danger'),

                
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'En cours',
                        'closed' => 'Clôturée',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
{
    return [
        'index' => Pages\ListAcademicYears::route('/'),
        'create' => Pages\CreateAcademicYear::route('/create'),
        'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
    ];
}
}
