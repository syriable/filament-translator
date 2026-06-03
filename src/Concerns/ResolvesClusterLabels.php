<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use UnitEnum;

trait ResolvesClusterLabels
{
    use ResolvesConventionNamespace;

    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $conventionKey = static::resolveConventionNamespace().".{$key}";

        return static::lookupConventionKey($conventionKey, $replace, $number, $allowNull);
    }

    public static function getNavigationLabel(): string
    {
        return static::resolveLabel('navigation_label') ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::resolveLabel('navigation_group') ?? parent::getNavigationGroup();
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return static::resolveLabel('cluster_breadcrumb') ?? parent::getClusterBreadcrumb();
    }
}
