<?php

namespace App\Filament\Resources\CourseScheduleResource\Pages;

use App\Filament\Resources\CourseScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseSchedules extends ListRecords
{
    protected static string $resource = CourseScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
