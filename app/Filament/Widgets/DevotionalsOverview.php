<?php

namespace App\Filament\Widgets;

use App\Models\Devotional;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DevotionalsOverview extends StatsOverviewWidget
{
    private $devotionals_days;

    private $devotionals_months;

    protected function getStats(): array
    {

        $this->devotionals_days = Devotional::query()->count();
        $this->devotionals_months = Devotional::query()->distinct('month')->count();

        return [
            Stat::make('Dias Cadastrados', $this->devotionals_days),
            Stat::make('Meses Cadastrados', $this->devotionals_months),
            // Stat::make('Average time on page', '3:12'),
        ];
    }
}
