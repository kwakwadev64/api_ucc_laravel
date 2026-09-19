<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use Illuminate\Database\Eloquent\Collection;

class AcademicCalendarService
{
    public function getCalendars(): Collection
    {
        return AcademicCalendar::with([
            'academicYear',
            'uploader',
        ])
        ->latest()
        ->get();
    }

    public function getCalendar(
        AcademicCalendar $academicCalendar
    ): AcademicCalendar {
        return $academicCalendar->load([
            'academicYear',
            'uploader',
        ]);
    }
}
