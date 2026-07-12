<?php

namespace App\Filament\Resources\ExamScheduleResource\Pages;

use App\Filament\Resources\ExamScheduleResource;
use App\Models\Schedule;
use Filament\Resources\Pages\CreateRecord;

class CreateExamSchedule extends CreateRecord
{
    protected static string $resource = ExamScheduleResource::class;


    /**
     * Ajouter automatiquement le type examen
     * avant l'enregistrement.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = Schedule::TYPE_EXAM;

        return $data;
    }


    /**
     * Message après création.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Horaire d’examen créé avec succès.';
    }
}
