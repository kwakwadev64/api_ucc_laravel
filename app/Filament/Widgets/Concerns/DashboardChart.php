<?php

namespace App\Filament\Widgets\Concerns;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

abstract class DashboardChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use InteractsWithDashboardFilters;

    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 1;
}
