<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use Syriable\Filament\Plugins\Translator\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Locate a field by name within a (possibly nested) schema. Shared by the resolver harness tests.
 */
function schemaField(Schema $schema, string $name): TextInput
{
    foreach ($schema->getFlatComponents() as $component) {
        if ($component instanceof TextInput && $component->getName() === $name) {
            return $component;
        }
    }

    throw new RuntimeException("Field [{$name}] not found.");
}

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
    $reset(ConventionRegistry::class, 'modelCastsCache');

    foreach ($conventionClasses as $class) {
        $reset($class, 'conventionKeyExistenceCache');
    }
}
