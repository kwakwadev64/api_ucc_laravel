<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionRequestResource\Pages;
use App\Models\PromotionRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PromotionRequestResource extends Resource
{
    protected static ?string $model = PromotionRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Demandes de Promotion';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Étudiant')
                    ->formatStateUsing(fn ($record) => $record->user?->first_name . ' ' . $record->user?->last_name)
                    ->searchable(['first_name', 'last_name']),
                
                Tables\Columns\TextColumn::make('currentPromotion.name')
                    ->label('Promotion Actuelle'),

                Tables\Columns\TextColumn::make('targetPromotion.name')
                    ->label('Promotion Demandée'),

                Tables\Columns\ImageColumn::make('proof_file')
                    ->label('Preuve (Relevé)')
                    ->disk('public'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de demande')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                    ])
            ])
            ->actions([
                // Action pour ACCEPTER la demande
                Tables\Actions\Action::make('approve')
                    ->label('Accepter')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (PromotionRequest $record) => $record->status === 'pending')
                    ->action(function (PromotionRequest $record) {
                        // 1. Mettre à jour la promotion de l'étudiant dans la table users
                        $user = $record->user;
                        $user->update([
                            'promotion_id' => $record->target_promotion_id,
                            'faculty_id' => $record->targetPromotion->faculty_id, // Met à jour la faculté si nécessaire
                        ]);

                        // 2. Mettre le statut de la demande à approved
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Demande acceptée')
                            ->body("L'étudiant a été promu avec succès.")
                            ->success()
                            ->send();
                    }),

                // Action pour REFUSER la demande
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        Forms\Components\Textarea::make('admin_comment')
                            ->label('Motif du rejet')
                            ->required()
                    ])
                    ->visible(fn (PromotionRequest $record) => $record->status === 'pending')
                    ->action(function (PromotionRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_comment' => $data['admin_comment'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Demande rejetée')
                            ->body('La demande a été rejetée.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePromotionRequests::route('/'),
        ];
    }
}