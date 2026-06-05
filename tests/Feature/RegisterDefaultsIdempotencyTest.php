<?php

use Filament\Actions\Action;
use Filament\Support\Components\ComponentManager;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;

/**
 * Issues #38 / #55 / #67 / #69 — registerDefaults() must be idempotent for the same component
 * manager and configuration (panel-plugin boot + manual host boot, and repeated boots inside a
 * long-lived Octane/Swoole worker), while still re-registering when configuration changes.
 */
function actionImportantConfigCount(): int
{
    $manager = ComponentManager::resolve();

    /** @var array<class-string, array<Closure>> $configurations */
    $configurations = invade($manager)->importantConfigurations;

    return count($configurations[Action::class] ?? []);
}

beforeEach(function () {
    ConventionRegistry::flushRegistrationState();
});

it('registers defaults only once for the same manager and configuration', function () {
    app(ConventionRegistry::class)->registerDefaults();
    $afterFirst = actionImportantConfigCount();

    // Simulate the duplicate plugin/host boot and repeated Octane worker boots.
    app(ConventionRegistry::class)->registerDefaults();
    app(ConventionRegistry::class)->registerDefaults();

    expect($afterFirst)->toBeGreaterThan(0)
        ->and(actionImportantConfigCount())->toBe($afterFirst);
});

it('re-registers when the configuration signature changes', function () {
    app(ConventionRegistry::class)->registerDefaults();
    $afterFirst = actionImportantConfigCount();

    config()->set('filament-translator.required', ['tooltip' => true]);
    app(ConventionRegistry::class)->registerDefaults();

    expect(actionImportantConfigCount())->toBeGreaterThan($afterFirst);
});
