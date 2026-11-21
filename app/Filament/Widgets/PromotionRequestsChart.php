<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\PromotionRequest;
use Illuminate\Database\Eloquent\Builder;

class PromotionRequestsChart extends DashboardChart
{
    protected static ?string $heading = 'Passations · demandes par statut';
    protected static ?int $sort = 30;
    protected static ?string $description = 'Comparaison des décisions enregistrées et du volume en attente.';

    protected function getData(): array
    {
        $counts = $this->requestsQuery()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $statuses = ['pending' => 'En attente', 'approved' => 'Approuvées', 'rejected' => 'Rejetées'];

        return ['labels' => array_values($statuses), 'datasets' => [[
            'data' => array_map(fn ($key) => (int) ($counts[$key] ?? 0), array_keys($statuses)),
            'backgroundColor' => ['#B77932', '#176B68', '#A94742'], 'borderWidth' => 0,
        ]]];
    }

    protected function getType(): string { return 'doughnut'; }

    protected function requestsQuery(): Builder
    {
        return PromotionRequest::query()
            ->when($this->selectedFilter('academic_year_id'), fn (Builder $q, int $id) => $q->where('academic_year_id', $id))
            ->when($this->selectedFilter('faculty_id'), fn (Builder $q, int $id) => $q->whereHas('currentPromotion', fn (Builder $p) => $p->where('faculty_id', $id)))
            ->when($this->selectedFilter('promotion_id'), fn (Builder $q, int $id) => $q->where(fn (Builder $p) => $p->where('current_promotion_id', $id)->orWhere('target_promotion_id', $id)));
    }
}
