<?php

use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use Syriable\Filament\Plugins\Translator\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Clear the static translation/existence caches that intentionally survive a request, so each
 * test starts from a clean slate and can register lines without seeing a stale "missing" result.
 *
 * @param  array<int, class-string>  $conventionClasses  Classes whose per-class existence cache to clear.
 */
function resetTranslatorCaches(array $conventionClasses = []): void
{
    $reset = static function (string $class, string $property): void {
        if (! property_exists($class, $property)) {
            return;
        }

        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    };

    $reset(ConventionRegistry::class, 'translatorHasCache');
    $reset(ConventionRegistry::class, 'prebuiltComponentCache');

    foreach ($conventionClasses as $class) {
        $reset($class, 'conventionKeyExistenceCache');
    }
}
