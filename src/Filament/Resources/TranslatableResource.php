<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Resources;

use Filament\Resources\Resource as BaseResource;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourceLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

class TranslatableResource extends BaseResource implements TranslatesConventionally
{
    use ResolvesResourceLabels;
}
