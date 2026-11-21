<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\Course;

class CoursePublicationChart extends DashboardChart
{
    protected static ?string $heading = 'Activité pédagogique · état des cours';
    protected static ?int $sort = 40;
    protected static ?string $description = 'Cours publiés et non publiés dans le périmètre sélectionné.';

    protected function getData(): array
    {
        $counts = $this->applyAcademicContext(Course::query(), 'academic_year_id', 'promotion')
            ->selectRaw('is_published, COUNT(*) as total')->groupBy('is_published')->pluck('total', 'is_published');

        return ['labels' => ['Publiés', 'Non publiés'], 'datasets' => [[
            'data' => [(int) ($counts[1] ?? 0), (int) ($counts[0] ?? 0)],
            'backgroundColor' => ['#176B68', '#C7CFD2'], 'borderWidth' => 0,
        ]]];
    }

    protected function getType(): string { return 'doughnut'; }
}
