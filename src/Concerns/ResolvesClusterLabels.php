<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Facades\Filament;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use UnitEnum;

trait ResolvesClusterLabels
{
    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $namespace = str(static::class);

        $panel = Filament::getCurrentOrDefaultPanel();
        $pathAliases = TranslatorPlugin::get($panel)->getPathAliases();

        if ($namespace->contains(array_keys($pathAliases))) {
            $namespace = $namespace->replace(
                array_keys($pathAliases),
                array_values($pathAliases),
            );
        } else {
            $namespace = $namespace
                ->after('Filament')
                ->prepend('Filament')
                ->trim('\\');
        }

        $namespace = $namespace
            ->kebab()
            ->replace('\\-', '\\')
            ->replace('\\', '/')
            ->rtrim('/');

        $conventionKey = "{$namespace}.{$key}";

        if (! app('translator')->has($conventionKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($conventionKey, $number, $replace);
        }

        return __($conventionKey, $replace);
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
