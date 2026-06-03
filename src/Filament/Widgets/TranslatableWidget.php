<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Widgets;

use Filament\Widgets\Widget as BaseWidget;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesWidgetLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableWidget extends BaseWidget implements TranslatesConventionally
{
    use ResolvesWidgetLabels;
}
