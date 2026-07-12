<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;


    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static ?string $navigationGroup = 'Horaires';


    protected static ?string $navigationLabel = 'Horaires des examens';


    protected static ?string $modelLabel = 'Horaire d’examen';


    protected static ?string $pluralModelLabel = 'Horaires des examens';



    /**
     * Afficher uniquement les horaires d'examen.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', Schedule::TYPE_EXAM);
    }




    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\Select::make('faculty_id')
                    ->label('Faculté')
                    ->relationship('faculty', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),



                Forms\Components\Select::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),



                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'name')
                    ->searchable()
                    ->preload(),



                Forms\Components\Select::make('program_id')
                    ->label('Programme / Filière')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),



                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->default('Horaire des examens')
                    ->required()
                    ->maxLength(255),



                Forms\Components\FileUpload::make('file_path')
                    ->label('Fichier horaire examen')
                    ->disk('public')
                    ->directory('schedules/exams')
                    ->preserveFilenames()
                    ->acceptedFileTypes([

                        'application/pdf',

                        'image/jpeg',

                        'image/png',

                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                    ])
                    ->required(),



                /*
                |--------------------------------------------------------------------------
                | Type caché
                |--------------------------------------------------------------------------
                */
                Forms\Components\Hidden::make('type')
                    ->default(Schedule::TYPE_EXAM),



                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),


            ]);
    }





    public static function table(Table $table): Table
    {
        return $table
            ->columns([


                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),



                Tables\Columns\TextColumn::make('faculty.name')
                    ->label('Faculté')
                    ->sortable(),



                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable(),



                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->placeholder('Toutes'),



                Tables\Columns\TextColumn::make('program.name')
                    ->label('Programme / Filière')
                    ->placeholder('Toutes'),



                Tables\Columns\TextColumn::make('file_type')
                    ->label('Format'),



                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),



                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),

            ])



            ->filters([

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

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





    public static function getRelations(): array
    {
        return [];
    }





    public static function getPages(): array
    {
        return [

            'index' => Pages\ListExamSchedules::route('/'),

            'create' => Pages\CreateExamSchedule::route('/create'),

            'edit' => Pages\EditExamSchedule::route('/{record}/edit'),

        ];
    }
}
