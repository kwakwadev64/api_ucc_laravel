<?php

namespace App\Http\Controllers\Api;

use App\Models\AcademicCalendar;
use App\Services\AcademicCalendarService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;


class AcademicCalendarController extends Controller
{
    public function __construct(
        private AcademicCalendarService $academicCalendarService
    ) {
    }

    public function index(): JsonResponse
    {
        $calendars = $this->academicCalendarService
            ->getCalendars();

        return response()->json([
            'success' => true,
            'data' => $calendars->map(
                fn (AcademicCalendar $calendar) =>
                    $this->formatCalendar($calendar)
            ),
        ]);
    }

    public function show(
        AcademicCalendar $academicCalendar
    ): JsonResponse {
        $calendar = $this->academicCalendarService
            ->getCalendar($academicCalendar);

        return response()->json([
            'success' => true,
            'data' => $this->formatCalendar($calendar),
        ]);
    }

    private function formatCalendar(
        AcademicCalendar $calendar
    ): array {
        return [
            'id' => $calendar->id,

            'academic_year' => $calendar->academicYear ? [
                'id' => $calendar->academicYear->id,
                'name' => $calendar->academicYear->name,
            ] : null,

            'file_url' => $calendar->file_path
                ? asset('storage/' . $calendar->file_path)
                : null,

            'file_type' => $calendar->file_type,

            'uploaded_by' => $calendar->uploader ? [
                'id' => $calendar->uploader->id,
                'name' => trim(
                    $calendar->uploader->first_name . ' ' .
                    $calendar->uploader->last_name
                ),
            ] : null,

            'created_at' => $calendar->created_at,
            'updated_at' => $calendar->updated_at,
        ];
    }
}
