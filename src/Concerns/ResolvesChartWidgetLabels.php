<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;

/**
 * @mixin ChartWidget
 */
trait ResolvesChartWidgetLabels
{
    use ResolvesWidgetLabels;

    public function getHeading(): string|Htmlable|null
    {
        return static::resolveLabel('heading', allowNull: true);
    }
}
