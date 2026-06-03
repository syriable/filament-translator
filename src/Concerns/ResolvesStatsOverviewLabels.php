<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;
use Syriable\Filament\Plugins\Translator\Filament\Widgets\StatsOverviewWidget;

/**
 * @mixin StatsOverviewWidget
 */
trait ResolvesStatsOverviewLabels
{
    use ResolvesWidgetLabels;

    protected function getHeading(): ?string
    {
        return static::resolveLabel('heading', allowNull: true) ?? parent::getHeading();
    }

    protected function getDescription(): ?string
    {
        return static::resolveLabel('description', allowNull: true) ?? parent::getDescription();
    }
}
