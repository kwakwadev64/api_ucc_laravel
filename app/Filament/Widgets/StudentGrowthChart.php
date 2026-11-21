<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Filament\Widgets\Concerns\DashboardChart;

class StudentGrowthChart extends DashboardChart
{
    protected static ?string $heading = 'Population étudiante · évolution des comptes';
    protected static ?int $sort = 10;
    protected static ?string $description = 'Nouveaux comptes étudiants par mois, sur les 12 derniers mois.';

    protected function getData(): array
    {
        [$labels, $counts] = $this->monthlyCounts($this->applyStudentFilters(User::query()), 'created_at');

        return ['datasets' => [[
            'label' => 'Nouveaux comptes', 'data' => $counts,
            'borderColor' => '#176B68', 'backgroundColor' => 'rgba(23, 107, 104, .14)',
            'fill' => true, 'tension' => .35,
        ]], 'labels' => $labels];
    }

    protected function getType(): string { return 'line'; }
}
