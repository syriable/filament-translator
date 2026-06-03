<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Facades\Filament;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;

trait ResolvesWidgetLabels
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
}
