<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesTableWidgetLabels;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableTableWidget extends BaseWidget implements TranslatesConventionally
{
    use ResolvesTableWidgetLabels;
    use ResolvesWidgetLabels;
}
