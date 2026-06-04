<?php

use Filament\Facades\Filament;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;
use TranslatorFixtures\App\Filament\Resources\UserResource;

it('derives a dotted lang path from the class namespace', function () {
    expect(UserResource::exposedConventionNamespace())->toBe('filament/resources/user-resource');
});

it('applies registered path aliases to the namespace', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(
        TranslatorPlugin::make()->pathAliases([
            'TranslatorFixtures\\App\\Filament\\Resources' => 'custom',
        ]),
    );

    expect(UserResource::exposedConventionNamespace())->toBe('custom/user-resource');
});

it('resolves an existing convention key to its translation', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.cn_present' => 'Members',
    ], 'en');

    expect(UserResource::exposedLookup('filament/resources/user-resource.cn_present'))->toBe('Members');
});

it('returns null for a missing key when null is allowed', function () {
    expect(UserResource::exposedLookup('filament/resources/user-resource.cn_absent_a', allowNull: true))->toBeNull();
});

it('returns the key itself for a missing key when null is not allowed (non-production)', function () {
    expect(UserResource::exposedLookup('filament/resources/user-resource.cn_absent_b'))
        ->toBe('filament/resources/user-resource.cn_absent_b');
});

it('returns null for a missing key in production', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(UserResource::exposedLookup('filament/resources/user-resource.cn_absent_c'))->toBeNull();
});

it('uses trans_choice when a number is provided', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.cn_apples' => '{1} apple|[2,*] apples',
    ], 'en');

    expect(UserResource::exposedLookup('filament/resources/user-resource.cn_apples', number: 1))->toBe('apple')
        ->and(UserResource::exposedLookup('filament/resources/user-resource.cn_apples', number: 3))->toBe('apples');
});

it('reports whether a convention key exists', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.cn_exists' => 'Yes',
    ], 'en');

    expect(UserResource::exposedExists('filament/resources/user-resource.cn_exists'))->toBeTrue()
        ->and(UserResource::exposedExists('filament/resources/user-resource.cn_missing_existence'))->toBeFalse();
});
