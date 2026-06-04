<?php

use Filament\Actions\Action;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use Syriable\Filament\Plugins\Translator\Tests\Fixtures\PrebuiltStub;

/**
 * Invoke a protected static helper on the registry via reflection.
 */
function invokeRegistry(string $method, array $args)
{
    $reflection = new ReflectionMethod(ConventionRegistry::class, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke(null, ...$args);
}

it('reports translator availability and caches the lookup', function () {
    app('translator')->addLines([
        'registry/has.key' => 'present',
    ], 'en');

    expect(invokeRegistry('translatorHas', ['registry/has.key']))->toBeTrue()
        ->and(invokeRegistry('translatorHas', ['registry/has.missing']))->toBeFalse();
});

it('builds and resolves an absolute key', function () {
    app('translator')->addLines([
        'globals.active' => 'Active',
    ], 'en');

    expect(invokeRegistry('lookupAbsoluteKey', ['globals', 'active']))->toBe('Active');
});

it('returns null for a missing absolute key when null is allowed', function () {
    expect(invokeRegistry('lookupAbsoluteKey', ['globals', 'missing_abs', [], null, true]))->toBeNull();
});

it('resolves an absolute key with pluralization', function () {
    app('translator')->addLines([
        'globals.apples' => '{1} apple|[2,*] apples',
    ], 'en');

    expect(invokeRegistry('lookupAbsoluteKey', ['globals', 'apples', [], 2]))->toBe('apples');
});

it('memoizes prebuilt components and runs setUp once', function () {
    $first = invokeRegistry('prebuiltComponent', [PrebuiltStub::class, 'alpha']);
    $second = invokeRegistry('prebuiltComponent', [PrebuiltStub::class, 'alpha']);

    expect($first)->toBeInstanceOf(PrebuiltStub::class)
        ->and($first->wasSetUp)->toBeTrue()
        ->and($first->name)->toBe('alpha')
        ->and($second)->toBe($first);
});

it('returns null from resolveActionLabel when the livewire owner does not translate conventionally', function () {
    expect(ConventionRegistry::resolveActionLabel(Action::make('test'), null, 'label'))->toBeNull();
});

it('registers component defaults without throwing', function () {
    expect(fn () => app(ConventionRegistry::class)->registerDefaults())->not->toThrow(Throwable::class);
});
