<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;


    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';


    protected static ?string $navigationGroup = 'Horaires';


    protected static ?string $navigationLabel = 'Horaires des cours';


    protected static ?string $modelLabel = 'Horaire de cours';


    protected static ?string $pluralModelLabel = 'Horaires des cours';



    /**
     * Filtrer uniquement les horaires de cours.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', Schedule::TYPE_COURSE);
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
                    ->default('Horaire des cours')
                    ->required(),



                Forms\Components\FileUpload::make('file_path')
                    ->label('Fichier horaire')
                    ->disk('public')
                    ->directory('schedules/courses')
                    ->preserveFilenames()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->required(),



                Forms\Components\Hidden::make('type')
                    ->default(Schedule::TYPE_COURSE),



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
                    ->searchable(),



                Tables\Columns\TextColumn::make('faculty.name')
                    ->label('Faculté'),



                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique'),



                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->placeholder('Toutes'),



                Tables\Columns\TextColumn::make('program.name')
                    ->label('Programme')
                    ->placeholder('Toutes'),



                Tables\Columns\TextColumn::make('file_type')
                    ->label('Type fichier'),



                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

            ]);
    }




    public static function getRelations(): array
    {
        return [];
    }




    public static function getPages(): array
    {
        return [

            'index' => Pages\ListCourseSchedules::route('/'),

            'create' => Pages\CreateCourseSchedule::route('/create'),

            'edit' => Pages\EditCourseSchedule::route('/{record}/edit'),

        ];
    }
}
