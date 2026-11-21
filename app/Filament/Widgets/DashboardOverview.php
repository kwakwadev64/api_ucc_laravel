<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDashboardFilters;
use App\Models\PromotionRequest;
use App\Models\Recours;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use InteractsWithDashboardFilters;

    protected ?string $heading = 'Vue générale';
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $students = $this->applyStudentFilters(User::query());
        $recours = $this->applyAcademicContext(Recours::query(), 'academic_year_id', 'promotion');
        $promotionRequests = PromotionRequest::query()
            ->when($this->selectedFilter('academic_year_id'), fn ($q, int $id) => $q->where('academic_year_id', $id))
            ->when($this->selectedFilter('faculty_id'), fn ($q, int $id) => $q->whereHas('currentPromotion', fn ($p) => $p->where('faculty_id', $id)))
            ->when($this->selectedFilter('promotion_id'), fn ($q, int $id) => $q->where(fn ($p) => $p->where('current_promotion_id', $id)->orWhere('target_promotion_id', $id)));

        return [
            Stat::make('Étudiants', number_format($students->count(), 0, ',', ' '))
                ->description('Comptes étudiants correspondant aux filtres')
                ->color('primary'),
            Stat::make('Recours à suivre', number_format((clone $recours)->whereIn('status', ['submitted', 'under_review'])->count(), 0, ',', ' '))
                ->description('Soumis ou en cours d’examen')
                ->color('warning'),
            Stat::make('Passations en attente', number_format((clone $promotionRequests)->where('status', 'pending')->count(), 0, ',', ' '))
                ->description('Demandes nécessitant une décision')
                ->color('danger'),
        ];
    }
}
