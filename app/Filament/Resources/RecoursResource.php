<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecoursResource\Pages;
use App\Models\AcademicYear;
use App\Models\Recours;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecoursResource extends Resource
{
    protected static ?string $model = Recours::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Recours R';

    protected static ?string $modelLabel = 'recours';

    protected static ?string $pluralModelLabel = 'recours';

    protected static ?string $navigationGroup = 'Gestion académique';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('post_name')
                    ->label('Post-nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'draft' => 'Brouillon',
                            'submitted' => 'Soumis',
                            'under_review' => 'En examen',
                            'decided' => 'Décidé',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'submitted' => 'warning',
                            'under_review' => 'info',
                            'decided' => 'success',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Date de soumission')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship('academicYear', 'name')
                    ->default(
                        fn () => AcademicYear::query()
                            ->where('status', 'active')
                            ->value('id')
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'submitted' => 'Soumis',
                        'under_review' => 'En examen',
                        'decided' => 'Décidé',
                    ]),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir'),
            ])

            ->bulkActions([])

            ->defaultSort('submitted_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecours::route('/'),
            'view' => Pages\ViewRecours::route('/{record}'),
        ];
    }
}
