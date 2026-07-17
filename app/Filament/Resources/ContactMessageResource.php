<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\Mail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ContactMessageResource extends Resource
{
    protected static ?string $model = Mail::class;

    // Configuration de l'affichage dans le menu latéral
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Messages Reçus';
    protected static ?string $modelLabel = 'Message de contact';
    protected static ?string $pluralModelLabel = 'Messages de contact';
    protected static ?string $navigationGroup = 'Gestion du Site';

    /**
     * Badge de notification affichant le nombre de messages non lus dans le menu
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('lu', false)->count() ?: null;
    }

    protected static ?string $navigationBadgeColor = 'danger';

    /**
     * Configuration de la vue de lecture du message
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails du Message')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->disabled(), // Lecture seule
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Adresse Email')
                            ->email()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('subject')
                            ->label('Sujet du message')
                            ->disabled()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('message')
                            ->label('Contenu du message')
                            ->disabled()
                            ->rows(6)
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('lu')
                            ->label('Marqué comme lu')
                            ->disabled(), // Géré par l'action automatique, mais affiché pour info
                    ])->columns(2)
            ]);
    }

    /**
     * Configuration de la liste des messages
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Expéditeur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sujet')
                    ->label('Sujet')
                    ->limit(30)
                    ->searchable(),

                // Colonne de statut sous forme de Badge stylisé
                Tables\Columns\IconColumn::make('est_lu')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Les plus récents en premier
            ->filters([
                // Filtre rapide pour isoler les messages non lus
                Filter::make('Non lus')
                    ->query(fn (Builder $query) => $query->where('est_lu', false))
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lire'),
                
                // Action personnalisée pour marquer comme lu directement depuis la liste
                Tables\Actions\Action::make('marquerCommeLu')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Mail $record) => !$record->lu)
                    ->action(fn (Mail $record) => $record->update(['lu' => true])),
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
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}