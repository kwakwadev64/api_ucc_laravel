<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardFilters
{
    protected function applyStudentFilters(Builder $query): Builder
    {
        return $query
            ->where('users.role', 'student')
            ->when($this->selectedFilter('academic_year_id'), fn (Builder $q, int $id) => $q->where('users.academic_year_id', $id))
            ->when($this->selectedFilter('faculty_id'), fn (Builder $q, int $id) => $q->where('users.faculty_id', $id))
            ->when($this->selectedFilter('promotion_id'), fn (Builder $q, int $id) => $q->where('users.promotion_id', $id));
    }

    protected function selectedFilter(string $key): ?int
    {
        $value = $this->filters[$key] ?? null;

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    protected function applyAcademicContext(Builder $query, string $yearColumn, string $promotionRelation): Builder
    {
        return $query
            ->when($this->selectedFilter('academic_year_id'), fn (Builder $q, int $id) => $q->where($yearColumn, $id))
            ->when($this->selectedFilter('faculty_id'), fn (Builder $q, int $id) => $q->whereHas($promotionRelation, fn (Builder $p) => $p->where('faculty_id', $id)))
            ->when($this->selectedFilter('promotion_id'), fn (Builder $q, int $id) => $q->whereHas($promotionRelation, fn (Builder $p) => $p->whereKey($id)));
    }

    protected function monthlyCounts(Builder $query, string $dateColumn, ?int $months = null): array
    {
        $months = $months ?? (in_array((int) ($this->filters['period'] ?? 12), [6, 12, 24], true) ? (int) ($this->filters['period'] ?? 12) : 12);
        $start = now()->startOfMonth()->subMonths($months - 1);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', {$dateColumn})"
            : "DATE_FORMAT({$dateColumn}, '%Y-%m')";

        $counts = $query
            ->whereNotNull($dateColumn)
            ->where($dateColumn, '>=', $start)
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as aggregate")
            ->groupBy('month_key')
            ->pluck('aggregate', 'month_key');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M y');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return [$labels, $data];
    }
}
