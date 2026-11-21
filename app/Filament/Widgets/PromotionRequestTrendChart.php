<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\DashboardChart;
use App\Models\PromotionRequest;
use Illuminate\Database\Eloquent\Builder;

class PromotionRequestTrendChart extends DashboardChart
{
    protected static ?string $heading = 'Passations · volume par année académique';
    protected static ?int $sort = 31;
    protected static ?string $description = 'Comparaison des demandes enregistrées dans chaque année académique.';

    protected function getData(): array
    {
        $query = PromotionRequest::query()
            ->when($this->selectedFilter('academic_year_id'), fn (Builder $q, int $id) => $q->where('promotion_requests.academic_year_id', $id))
            ->when($this->selectedFilter('faculty_id'), fn (Builder $q, int $id) => $q->whereHas('currentPromotion', fn (Builder $p) => $p->where('faculty_id', $id)))
            ->when($this->selectedFilter('promotion_id'), fn (Builder $q, int $id) => $q->where(fn (Builder $p) => $p->where('current_promotion_id', $id)->orWhere('target_promotion_id', $id)));
        $rows = $query->join('academic_years', 'promotion_requests.academic_year_id', '=', 'academic_years.id')
            ->selectRaw('academic_years.name as label, COUNT(promotion_requests.id) as total')
            ->groupBy('academic_years.id', 'academic_years.name', 'academic_years.start_date')
            ->orderBy('academic_years.start_date')->get();

        return ['labels' => $rows->pluck('label')->all(), 'datasets' => [[
            'label' => 'Demandes', 'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
            'backgroundColor' => '#345E83', 'borderRadius' => 5,
        ]]];
    }

    protected function getType(): string { return 'bar'; }
}
