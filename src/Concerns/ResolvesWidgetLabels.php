<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;

trait ResolvesWidgetLabels
{
    use ResolvesConventionNamespace;

    public static function resolveLabel(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $conventionKey = str(static::resolveConventionNamespace())
            ->when(filled($pageLabelContextKey))->append(".{$pageLabelContextKey}")
            ->when(blank($pageLabelContextKey) && $pageLabelContext, fn (Stringable $str) => $str->append(".{$pageLabelContext->value}"))
            ->append(".{$key}")
            ->toString();

        return static::lookupConventionKey($conventionKey, $replace, $number, $allowNull);
    }
}
