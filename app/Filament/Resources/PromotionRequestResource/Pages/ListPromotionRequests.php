<?php

namespace App\Filament\Resources\PromotionRequestResource\Pages;

use App\Filament\Resources\PromotionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionRequests extends ListRecords
{
    protected static string $resource = PromotionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
