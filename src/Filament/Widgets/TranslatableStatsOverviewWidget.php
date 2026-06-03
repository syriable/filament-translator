<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesStatsOverviewLabels;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableStatsOverviewWidget extends BaseWidget implements TranslatesConventionally
{
    use ResolvesStatsOverviewLabels;
    use ResolvesWidgetLabels;
}
