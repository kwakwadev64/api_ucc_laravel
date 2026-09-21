<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Str;

class StudentChatbotContextService
{
    public function __construct(
        private OfficialWebsiteContextService $officialWebsiteContextService,
        private ScheduleService $scheduleService
    ) {}

    public function build(User $student): string
    {
        $student->loadMissing(['faculty', 'promotion', 'academicYear']);

        $courses = Course::query()
            ->where('promotion_id', $student->promotion_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('is_published', true)
            ->latest()
            ->limit(10)
            ->get(['title', 'description']);

        $courseSchedules = $this->scheduleService
            ->getSchedulesFor($student, Schedule::TYPE_COURSE)
            ->take(5);

        $examSchedules = $this->scheduleService
            ->getSchedulesFor($student, Schedule::TYPE_EXAM)
            ->take(5);

        $lines = [
            'Informations issues exclusivement des sources officielles FSI-UCC :',
            $this->officialWebsiteContextService->build(),
            'Informations privées autorisées pour l’étudiant connecté :',
            'Faculté : '.($student->faculty?->name ?? 'non renseignée').'.',
            'Promotion : '.($student->promotion?->name ?? 'non renseignée').'.',
            'Année académique : '.($student->academicYear?->name ?? 'non renseignée').'.',
            'Cours publiés accessibles :',
        ];

        foreach ($courses as $course) {
            $description = Str::limit(
                Str::squish(strip_tags((string) $course->description)),
                500
            );

            $lines[] = '- '.$course->title
                .($description ? ' : '.$description : '.');
        }

        $lines[] = 'Horaires de cours accessibles :';
        foreach ($courseSchedules as $schedule) {
            $lines[] = '- '.$schedule->title.'.';
        }

        $lines[] = 'Horaires d’examens accessibles :';
        foreach ($examSchedules as $schedule) {
            $lines[] = '- '.$schedule->title.'.';
        }

        $lines[] = 'Le contenu des documents de cours et d’horaires n’est pas encore analysé par le chatbot.';

        return implode("\n", $lines);
    }
}
