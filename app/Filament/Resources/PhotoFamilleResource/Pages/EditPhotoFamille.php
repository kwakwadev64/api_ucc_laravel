<?php

namespace App\Filament\Resources\PhotoFamilleResource\Pages;

use App\Filament\Resources\PhotoFamilleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhotoFamille extends EditRecord
{
    protected static string $resource = PhotoFamilleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
