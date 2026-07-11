<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Cours';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('teacher_id')
                    ->label('Enseignant')
                    ->options(
                        User::query()
                            ->where('role', 'teacher')
                            ->get()
                            ->mapWithKeys(function ($user) {
                                return [
                                    $user->id => $user->first_name
                                        ? $user->first_name . ' ' . $user->last_name
                                        : 'Utilisateur sans nom'
                                ];
                            })
                    )
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\Select::make('academic_year_id')
                    ->label('Année académique')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\TextInput::make('title')
                    ->label('Titre du cours')
                    ->required()
                    ->maxLength(255),


                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),


               Forms\Components\FileUpload::make('file_path')
                    ->label('Fichier du cours')
                    ->disk('public')
                    ->directory('courses')
                    ->preserveFilenames()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                        'application/x-rar-compressed',
                        'image/jpeg',
                        'image/png',
                    ])->maxSize(102400),


                Forms\Components\TextInput::make('file_type')
                    ->label('Type du fichier')
                    ->disabled()
                    ->dehydrated(),


                Forms\Components\Toggle::make('is_published')
                    ->label('Publié')
                    ->default(false),

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


                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label('Enseignant')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->teacher
                            ? $record->teacher->first_name . ' ' . $record->teacher->last_name
                            : 'Non défini';
                    })
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('promotion.level')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable(),


                Tables\Columns\TextColumn::make('file_path')
                    ->label('Document')
                    ->formatStateUsing(fn ($state) => $state ? 'Ouvrir' : '-')
                    ->url(fn ($record) => $record->file_path
                        ? asset('storage/' . $record->file_path)
                        : null)
                    ->openUrlInNewTab(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([])

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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
