<?php

namespace App\Filament\Resources\RecoursResource\Pages;

use App\Filament\Resources\RecoursResource;
use Filament\Resources\Pages\ListRecords;

class ListRecours extends ListRecords
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
