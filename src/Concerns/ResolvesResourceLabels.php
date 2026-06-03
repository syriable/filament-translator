<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Facades\Filament;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use UnitEnum;

trait ResolvesResourceLabels
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

        $conventionKey = str($namespace)
            ->when(filled($pageLabelContextKey))->append(".{$pageLabelContextKey}")
            ->when(blank($pageLabelContextKey) && $pageLabelContext, fn (Stringable $str) => $str->append(".{$pageLabelContext->value}"))
            ->append(".{$key}")
            ->toString();

        if (! app('translator')->has($conventionKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($conventionKey, $number, $replace);
        }

        return __($conventionKey, $replace);
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
