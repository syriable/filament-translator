<?php

namespace Syriable\Filament\Plugins\Translator\Contracts;

use Countable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;

/**
 * Marker for classes whose static labels are resolved via convention-based lang keys.
 */
interface TranslatesConventionally
{
    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed;
}
