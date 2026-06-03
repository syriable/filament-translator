<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\Pages;

use Filament\Resources\Pages\ListRecords as BasePage;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourcePageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

class TranslatableListRecords extends BasePage implements TranslatesConventionally
{
    use ResolvesResourcePageLabels;
}
