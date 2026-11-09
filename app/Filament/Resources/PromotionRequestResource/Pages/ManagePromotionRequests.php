<?php

namespace App\Filament\Resources\PromotionRequestResource\Pages;

use App\Filament\Resources\PromotionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePromotionRequests extends ManageRecords
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // Décommente si l'admin doit pouvoir créer une demande manuellement
        ];
    }
}