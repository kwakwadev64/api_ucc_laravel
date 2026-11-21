<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\Recours;

class RecoursTrendChart extends DashboardChart
{
    protected static ?string $heading = 'Activité administrative · évolution des recours';
    protected static ?int $sort = 21;
    protected static ?string $description = 'Recours enregistrés par mois, selon leur date de soumission.';

    protected function getData(): array
    {
        [$labels, $counts] = $this->monthlyCounts(
            $this->applyAcademicContext(Recours::query(), 'academic_year_id', 'promotion'),
            'submitted_at',
        );

        return ['labels' => $labels, 'datasets' => [[
            'label' => 'Recours soumis', 'data' => $counts,
            'borderColor' => '#B77932', 'backgroundColor' => 'rgba(183, 121, 50, .16)',
            'fill' => true, 'tension' => .35,
        ]]];
    }

    protected function getType(): string { return 'line'; }
}
