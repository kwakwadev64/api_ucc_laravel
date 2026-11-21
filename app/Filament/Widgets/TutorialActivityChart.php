<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\Tutorial;

class TutorialActivityChart extends DashboardChart
{
    protected static ?string $heading = 'Activité pédagogique · publications de tutoriels';
    protected static ?int $sort = 41;
    protected static ?string $description = 'Tutoriels créés par mois sur les 12 derniers mois. Les tutoriels ne sont pas liés aux facultés ou années académiques.';

    protected function getData(): array
    {
        [$labels, $counts] = $this->monthlyCounts(Tutorial::query(), 'created_at');

        return ['labels' => $labels, 'datasets' => [[
            'label' => 'Tutoriels publiés', 'data' => $counts,
            'borderColor' => '#345E83', 'backgroundColor' => 'rgba(52, 94, 131, .16)',
            'fill' => true, 'tension' => .35,
        ]]];
    }

    protected function getType(): string { return 'line'; }
}
