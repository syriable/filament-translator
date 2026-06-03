<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Widgets;

use Filament\Widgets\ChartWidget as BaseWidget;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesChartWidgetLabels;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableChartWidget extends BaseWidget implements TranslatesConventionally
{
    use ResolvesChartWidgetLabels;
    use ResolvesWidgetLabels;
}
