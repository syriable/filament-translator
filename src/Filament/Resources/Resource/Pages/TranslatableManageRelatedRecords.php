<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\Pages;

use Filament\Resources\Pages\ManageRelatedRecords as BasePage;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourcePageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

class TranslatableManageRelatedRecords extends BasePage implements TranslatesConventionally
{
    use ResolvesResourcePageLabels;
}
