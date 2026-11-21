<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\User;

class StudentsByPromotionChart extends DashboardChart
{
    protected static ?string $heading = 'Population étudiante · répartition par promotion';
    protected static ?int $sort = 12;
    protected static ?string $description = 'Effectifs étudiants par promotion dans le contexte sélectionné.';

    protected function getData(): array
    {
        $rows = $this->applyStudentFilters(User::query())
            ->join('promotions', 'users.promotion_id', '=', 'promotions.id')
            ->selectRaw('promotions.name as label, COUNT(users.id) as total')
            ->groupBy('promotions.id', 'promotions.name')
            ->orderByDesc('total')->get();

        return ['labels' => $rows->pluck('label')->all(), 'datasets' => [[
            'label' => 'Étudiants', 'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
            'backgroundColor' => '#B77932', 'borderRadius' => 5,
        ]]];
    }

    protected function getType(): string { return 'bar'; }
}
