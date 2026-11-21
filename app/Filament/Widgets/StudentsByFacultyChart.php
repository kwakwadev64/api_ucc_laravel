<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\User;

class StudentsByFacultyChart extends DashboardChart
{
    protected static ?string $heading = 'Population étudiante · effectifs par faculté';
    protected static ?int $sort = 11;
    protected static ?string $description = 'Nombre de comptes étudiants rattachés à chaque faculté.';

    protected function getData(): array
    {
        $rows = $this->applyStudentFilters(User::query())
            ->join('faculties', 'users.faculty_id', '=', 'faculties.id')
            ->selectRaw('faculties.name as label, COUNT(users.id) as total')
            ->groupBy('faculties.id', 'faculties.name')
            ->orderByDesc('total')->get();

        return ['labels' => $rows->pluck('label')->all(), 'datasets' => [[
            'label' => 'Étudiants', 'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
            'backgroundColor' => '#176B68', 'borderRadius' => 5,
        ]]];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): ?array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]];
    }
}
