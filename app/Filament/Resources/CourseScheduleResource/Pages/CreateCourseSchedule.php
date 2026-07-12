<?php

namespace App\Filament\Resources\CourseScheduleResource\Pages;


use App\Filament\Resources\CourseScheduleResource;
use App\Models\Schedule;
use Filament\Resources\Pages\CreateRecord;


class CreateCourseSchedule extends CreateRecord
{


    protected static string $resource = CourseScheduleResource::class;



    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['type'] = Schedule::TYPE_COURSE;


        return $data;

    }



    protected function getCreatedNotificationTitle(): ?string
    {

        return "Horaire de cours créé avec succès.";

    }

}
