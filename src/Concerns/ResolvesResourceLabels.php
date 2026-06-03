<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use UnitEnum;

trait ResolvesResourceLabels
{
    use ResolvesConventionNamespace;

    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $conventionKey = str(static::resolveConventionNamespace())
            ->when(filled($pageLabelContextKey))->append(".{$pageLabelContextKey}")
            ->when(blank($pageLabelContextKey) && $pageLabelContext, fn (Stringable $str) => $str->append(".{$pageLabelContext->value}"))
            ->append(".{$key}")
            ->toString();

        return static::lookupConventionKey($conventionKey, $replace, $number, $allowNull);
    }

    public static function getModelLabel(): string
    {
        return static::resolveLabel('model_label', allowNull: true) ?? parent::getModelLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return static::resolveLabel('plural_model_label', allowNull: true) ?? parent::getPluralModelLabel();
    }

    public static function getNavigationLabel(): string
    {
        return static::resolveLabel('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::resolveLabel('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }

    public static function getBreadcrumb(): string
    {
        return static::resolveLabel('breadcrumb', allowNull: true) ?? parent::getBreadcrumb();
    }
}
