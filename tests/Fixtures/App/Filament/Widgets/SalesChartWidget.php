<?php

namespace TranslatorFixtures\App\Filament\Widgets;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesChartWidgetLabels;

/**
 * Fixture chart widget under a realistic `App\Filament\Widgets\...` namespace so its convention
 * namespace is `filament/widgets/sales-chart-widget`.
 */
class SalesChartWidget
{
    use ResolvesChartWidgetLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
