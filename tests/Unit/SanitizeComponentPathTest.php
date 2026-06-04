<?php

use Syriable\Filament\Plugins\Translator\ConventionRegistry;

/**
 * `sanitizeComponentPath()` is a protected static helper; invoke it via reflection.
 */
function sanitizeComponentPath(string $name, array $namespace = []): string
{
    $method = new ReflectionMethod(ConventionRegistry::class, 'sanitizeComponentPath');
    $method->setAccessible(true);

    return $method->invoke(null, $name, $namespace);
}

it('strips livewire data/form/schema prefixes', function () {
    expect(sanitizeComponentPath('data.name'))->toBe('name')
        ->and(sanitizeComponentPath('form.email'))->toBe('email')
        ->and(sanitizeComponentPath('schema.title'))->toBe('title');
});

it('strips the mountedActionSchema segment', function () {
    expect(sanitizeComponentPath('mountedActionSchema0.field'))->toBe('field');
});

it('converts dotted paths to arrow notation', function () {
    expect(sanitizeComponentPath('address.street'))->toBe('address->street')
        ->and(sanitizeComponentPath('data.address.street'))->toBe('address->street');
});

it('leaves a plain name untouched', function () {
    expect(sanitizeComponentPath('name'))->toBe('name');
});

it('prepends a dotted namespace prefix', function () {
    expect(sanitizeComponentPath('name', ['actions', 'create']))->toBe('actions.create.name')
        ->and(sanitizeComponentPath('a.b', ['scope']))->toBe('scope.a->b');
});
