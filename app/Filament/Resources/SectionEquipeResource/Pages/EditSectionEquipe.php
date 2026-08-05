<?php

namespace App\Filament\Resources\SectionEquipeResource\Pages;

use App\Filament\Resources\SectionEquipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSectionEquipe extends EditRecord
{
    protected static string $resource = SectionEquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
