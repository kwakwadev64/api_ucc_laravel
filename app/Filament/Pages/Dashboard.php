<?php

namespace App\Filament\Pages;

use App\Models\AcademicYear;
use App\Models\Faculty;
use App\Models\Promotion;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Tableau de bord';

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Select::make('academic_year_id')
                ->label('Année académique')
                ->options(fn () => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id'))
                ->default(fn () => AcademicYear::query()->where('status', 'active')->value('id'))
                ->placeholder('Toutes les années')
                ->searchable(),
            Select::make('faculty_id')
                ->label('Faculté')
                ->options(fn () => Faculty::query()->orderBy('name')->pluck('name', 'id'))
                ->placeholder('Toutes les facultés')
                ->searchable(),
            Select::make('promotion_id')
                ->label('Promotion')
                ->options(fn () => Promotion::query()->orderBy('name')->pluck('name', 'id'))
                ->placeholder('Toutes les promotions')
                ->searchable(),
            Select::make('period')
                ->label('Période des tendances')
                ->options(['6' => '6 derniers mois', '12' => '12 derniers mois', '24' => '24 derniers mois'])
                ->default('12'),
        ])->columns(['md' => 2, 'xl' => 4]);
    }

    public function getColumns(): int | string | array
    {
        return ['md' => 2, 'xl' => 3];
    }
}
