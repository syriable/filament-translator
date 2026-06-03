<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager as BaseRelationManager;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesRelationManagerLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableRelationManager extends BaseRelationManager implements TranslatesConventionally
{
    use ResolvesRelationManagerLabels;
}
