<?php

use Filament\Facades\Filament;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;
use TranslatorFixtures\App\Filament\Resources\UserResource;

it('exposes a stable plugin id', function () {
    expect(TranslatorPlugin::make()->getId())->toBe('syriable/filament-translator');
});

it('builds an instance via make()', function () {
    expect(TranslatorPlugin::make())->toBeInstanceOf(TranslatorPlugin::class);
});

it('chains path aliases fluently', function () {
    $plugin = TranslatorPlugin::make()
        ->pathAliases(['App\\Livewire' => 'livewire'])
        ->pathAliases(['App\\Filament' => 'filament']);

    expect($plugin)->toBeInstanceOf(TranslatorPlugin::class)
        ->and($plugin->getPathAliases())->toBe([
            'App\\Livewire' => 'livewire',
            'App\\Filament' => 'filament',
        ]);
});

it('returns a default instance when the plugin is not registered on the panel', function () {
    $plugin = TranslatorPlugin::get(Filament::getCurrentOrDefaultPanel());

    expect($plugin)->toBeInstanceOf(TranslatorPlugin::class)
        ->and($plugin->getPathAliases())->toBe([])
        ->and(TranslatorPlugin::isActive())->toBeFalse();
});

it('returns the registered instance when present on the panel', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(
        TranslatorPlugin::make()->pathAliases(['App\\X' => 'x']),
    );

    expect(TranslatorPlugin::isActive())->toBeTrue()
        ->and(TranslatorPlugin::get()->getPathAliases())->toBe(['App\\X' => 'x']);
});

/**
 * Issue #54 — standalone Livewire path alias behaviour. With no active plugin the namespace falls
 * back to the default `Filament`-prefixed derivation; when a plugin with matching aliases is active,
 * the alias remaps the namespace.
 */
it('falls back to default namespace derivation when no plugin is active', function () {
    expect(TranslatorPlugin::isActive())->toBeFalse()
        ->and(UserResource::exposedConventionNamespace())->toBe('filament/resources/user-resource');
});

it('applies path aliases from the active plugin to namespace derivation', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(
        TranslatorPlugin::make()->pathAliases([
            'TranslatorFixtures\\App\\Filament\\Resources' => 'admin/resources',
        ]),
    );

    expect(UserResource::exposedConventionNamespace())->toBe('admin/resources/user-resource');
});
