<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\Recours;

class AdministrativeRequestsChart extends DashboardChart
{
    protected static ?string $heading = 'Activité administrative · recours par statut';
    protected static ?int $sort = 20;
    protected static ?string $description = 'Le modèle distingue les recours en brouillon, soumis, en examen et décidés.';

    protected function getData(): array
    {
        $counts = $this->applyAcademicContext(Recours::query(), 'academic_year_id', 'promotion')
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $statuses = ['draft' => 'Brouillon', 'submitted' => 'Soumis', 'under_review' => 'En examen', 'decided' => 'Décidé'];

        return ['labels' => array_values($statuses), 'datasets' => [[
            'data' => array_map(fn ($key) => (int) ($counts[$key] ?? 0), array_keys($statuses)),
            'backgroundColor' => ['#A7AFB4', '#B77932', '#345E83', '#176B68'], 'borderWidth' => 0,
        ]]];
    }

    protected function getType(): string { return 'doughnut'; }
}
