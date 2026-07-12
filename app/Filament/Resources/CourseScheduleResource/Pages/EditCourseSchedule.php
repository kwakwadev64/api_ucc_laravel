<?php

namespace App\Filament\Resources\CourseScheduleResource\Pages;

use App\Filament\Resources\CourseScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSchedule extends EditRecord
{
    protected static string $resource = CourseScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
