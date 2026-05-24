<?php

namespace App\Filament\Resources\PhotoFamilleResource\Pages;

use App\Filament\Resources\PhotoFamilleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotoFamilles extends ListRecords
{
    protected static string $resource = PhotoFamilleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
