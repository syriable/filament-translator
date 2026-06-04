<?php

namespace TranslatorFixtures\App\Filament\Resources;

use Countable;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourceLabels;

/**
 * Fixture living under a realistic `App\Filament\Resources\...` namespace so the
 * convention-namespace derivation produces the documented `filament/resources/user-resource`
 * key. Protected trait helpers are re-exposed publicly for assertion.
 */
class UserResource
{
    use ResolvesResourceLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }

    public static function exposedLookup(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false): mixed
    {
        return static::lookupConventionKey($key, $replace, $number, $allowNull);
    }

    public static function exposedExists(string $key): bool
    {
        return static::conventionKeyExists($key);
    }
}
