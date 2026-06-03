<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;
use UnitEnum;

/**
 * Resolves page-level metadata (title, navigation, subheading) from convention-based lang keys.
 *
 * Syriable Filament Translator derives keys from the owning class namespace. Register
 * {@see TranslatorPlugin::pathAliases()} when your Livewire or Filament classes live
 * outside the default `filament/...` path.
 */
trait ResolvesPageLabels
{
    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $namespace = str(static::class);

        $pathAliases = TranslatorPlugin::get()->getPathAliases();

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

    public function getTitle(): string
    {
        return static::resolveLabel('title', allowNull: true) ?? parent::getTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return static::resolveLabel('subheading', allowNull: true) ?? parent::getSubheading();
    }

    public static function getNavigationLabel(): string
    {
        return static::resolveLabel('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::resolveLabel('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }
}
