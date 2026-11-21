<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\User;

class StudentStatusChart extends DashboardChart
{
    protected static ?string $heading = 'Population étudiante · statut des comptes';
    protected static ?int $sort = 13;
    protected static ?string $description = 'Comptes marqués actifs ou inactifs dans la base.';

    protected function getData(): array
    {
        $counts = $this->applyStudentFilters(User::query())
            ->selectRaw('is_active, COUNT(*) as total')->groupBy('is_active')->pluck('total', 'is_active');

        return ['labels' => ['Actifs', 'Inactifs'], 'datasets' => [[
            'data' => [(int) ($counts[1] ?? 0), (int) ($counts[0] ?? 0)],
            'backgroundColor' => ['#176B68', '#C7CFD2'], 'borderWidth' => 0,
        ]]];
    }

    protected function getType(): string { return 'doughnut'; }
}
