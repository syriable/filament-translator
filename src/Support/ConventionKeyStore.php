<?php

namespace Syriable\Filament\Plugins\Translator\Support;

use Closure;
use WeakMap;

/**
 * Stores the per-component convention key metadata set through the `conventionKey()` /
 * `conventionKeyAbsolute()` macros.
 *
 * Filament component objects are third-party classes that do not declare these properties, so
 * assigning them directly triggers the PHP 8.4 "Creation of dynamic property" deprecation. A
 * {@see WeakMap} keyed by the component instance keeps the metadata off the object while letting
 * the entries be garbage-collected automatically once the component is destroyed.
 *
 * @internal
 */
final class ConventionKeyStore
{
    /**
     * @var WeakMap<object, string|Closure|null>|null
     */
    private static ?WeakMap $keys = null;

    /**
     * @var WeakMap<object, bool|Closure>|null
     */
    private static ?WeakMap $absolute = null;

    public static function setKey(object $component, string | Closure | null $key): void
    {
        self::keys()[$component] = $key;
    }

    public static function getKey(object $component): string | Closure | null
    {
        return self::keys()[$component] ?? null;
    }

    public static function setAbsolute(object $component, bool | Closure $isAbsolute): void
    {
        self::absolute()[$component] = $isAbsolute;
    }

    public static function getAbsolute(object $component): bool | Closure
    {
        return self::absolute()[$component] ?? false;
    }

    /**
     * @return WeakMap<object, string|Closure|null>
     */
    private static function keys(): WeakMap
    {
        if (self::$keys === null) {
            /** @var WeakMap<object, string|Closure|null> $map */
            $map = new WeakMap();

            self::$keys = $map;
        }

        return self::$keys;
    }

    /**
     * @return WeakMap<object, bool|Closure>
     */
    private static function absolute(): WeakMap
    {
        if (self::$absolute === null) {
            /** @var WeakMap<object, bool|Closure> $map */
            $map = new WeakMap();

            self::$absolute = $map;
        }

        return self::$absolute;
    }
}
