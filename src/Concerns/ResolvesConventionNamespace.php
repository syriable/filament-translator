<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Facades\Filament;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;

/**
 * Shared convention-key derivation and lang lookup used by every {@see resolveLabel()} trait.
 *
 * Centralising this logic keeps the namespace → lang-path mapping in one place and prevents the
 * per-trait copy drift that previously dropped the {@see TranslatorPlugin} import.
 */
trait ResolvesConventionNamespace
{
    /**
     * Memoized `translator()->has()` results, keyed by "{locale}|{key}", scoped per using-class.
     *
     * @var array<string, bool>
     */
    protected static array $conventionKeyExistenceCache = [];

    /**
     * Derive the dotted lang-file path for the current class
     * (for example `filament/resources/user-resource`).
     */
    protected static function resolveConventionNamespace(): string
    {
        $namespace = str(static::class);

        $pathAliases = TranslatorPlugin::get(Filament::getCurrentOrDefaultPanel())->getPathAliases();

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

        return $namespace
            ->kebab()
            ->replace('\\-', '\\')
            ->replace('\\', '/')
            ->rtrim('/')
            ->toString();
    }

    /**
     * Resolve a fully-qualified convention key to its translation, falling back to null when the
     * key is missing and either nulls are allowed or the app is running in production.
     */
    protected static function lookupConventionKey(string $conventionKey, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): mixed
    {
        if (! static::conventionKeyExists($conventionKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($conventionKey, $number, $replace);
        }

        return __($conventionKey, $replace);
    }

    protected static function conventionKeyExists(string $conventionKey): bool
    {
        $cacheKey = app()->getLocale().'|'.$conventionKey;

        return static::$conventionKeyExistenceCache[$cacheKey] ??= app('translator')->has($conventionKey);
    }
}
