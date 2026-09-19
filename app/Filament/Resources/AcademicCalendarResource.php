<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicCalendarResource\Pages;
use App\Models\AcademicCalendar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicCalendarResource extends Resource
{
    protected static ?string $model = AcademicCalendar::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendrier académique';

    protected static ?string $modelLabel = 'Calendrier académique';

    protected static ?string $pluralModelLabel = 'Calendriers académiques';

    protected static ?string $navigationGroup = 'Gestion académique';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship(
                        'academicYear',
                        'name'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Calendrier académique')
                    ->disk('public')
                    ->directory('academic-calendars')
                    ->acceptedFileTypes([
                        'application/pdf',
                    ])
                    ->maxSize(10240)
                    ->required()
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
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Format')
                    ->badge(),

                Tables\Columns\TextColumn::make('uploader.first_name')
                    ->label('Ajouté par')
                    ->formatStateUsing(
                        fn ($state, AcademicCalendar $record) => trim(
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
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship(
                        'academicYear',
                        'name'
                    ),
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
            'index' => Pages\ListAcademicCalendars::route('/'),
            'create' => Pages\CreateAcademicCalendar::route('/create'),
            'edit' => Pages\EditAcademicCalendar::route('/{record}/edit'),
        ];
    }
}
