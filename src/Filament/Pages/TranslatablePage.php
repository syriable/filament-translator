<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Pages;

use Filament\Pages\Page as BasePage;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesPageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatablePage extends BasePage implements TranslatesConventionally
{
    use ResolvesPageLabels;
}
