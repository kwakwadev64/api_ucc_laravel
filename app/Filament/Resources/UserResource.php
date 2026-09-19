<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    /**
     * Configuration du formulaire de création / édition
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([

                    Forms\Components\TextInput::make('first_name')
                        ->label('Prénom')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Nom')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Adresse E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create'),

                    Forms\Components\Select::make('role')
                        ->label('Rôle sur la plateforme')
                        ->options([
                            'student' => 'Étudiant',
                            'cp' => 'Chef de Promotion (CP)',
                            'teacher' => 'Enseignant',
                            'faculty_admin' => 'Admin Faculté',
                            'super_admin' => 'Super Administrateur',
                        ])
                        ->required(),

                    Forms\Components\Select::make('faculty_id')
                        ->label('Faculté rattachée')
                        ->relationship('faculty', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\Select::make('promotion_id')
                        ->label('Promotion (Classe)')
                        ->relationship('promotion', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\Select::make('academic_year_id')
                        ->label('Année académique')
                        ->relationship('academicYear', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Compte Actif')
                        ->default(true)
                        ->required(),

                ])->columns(2)
            ]);
    }

    /**
     * Configuration de la liste avec Recherche et Filtres
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'faculty_admin' => 'warning',
                        'teacher' => 'info',
                        'cp' => 'success',
                        'student' => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('faculty.name')
                    ->label('Faculté')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->sortable(),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Année académique')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('role')
                    ->label('Filtrer par rôle')
                    ->options([
                        'student' => 'Étudiant',
                        'cp' => 'Chef de Promotion (CP)',
                        'teacher' => 'Enseignant',
                        'faculty_admin' => 'Admin Faculté',
                        'super_admin' => 'Super Admin',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Statut du compte')
                    ->trueLabel('Comptes actifs uniquement')
                    ->falseLabel('Comptes bloqués uniquement')
                    ->placeholder('Tous les comptes'),

                SelectFilter::make('faculty_id')
                    ->label('Par Faculté')
                    ->relationship('faculty', 'name'),

                SelectFilter::make('academic_year_id')
                    ->label('Par année académique')
                    ->relationship('academicYear', 'name'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
