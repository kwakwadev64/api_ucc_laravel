<?php

namespace App\Filament\Resources\AcademicCalendarResource\Pages;

use App\Filament\Resources\AcademicCalendarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademicCalendar extends EditRecord
{
    protected static string $resource = AcademicCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
