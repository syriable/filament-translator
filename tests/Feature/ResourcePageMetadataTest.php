<?php

use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;
use TranslatorFixtures\App\Filament\Resources\UserResource;

/**
 * Issues #70 / #72 — resource and page metadata resolution (titles, navigation, breadcrumbs)
 * through the convention namespace.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class, UserResource::class]);
});

it('resolves resource model and plural labels from the convention namespace', function () {
    app('translator')->addLines([
        UserResource::exposedConventionNamespace() . '.model_label' => 'user',
        UserResource::exposedConventionNamespace() . '.plural_model_label' => 'users',
    ], 'en');

    expect(UserResource::resolveLabel('model_label'))->toBe('user')
        ->and(UserResource::resolveLabel('plural_model_label'))->toBe('users');
});

it('resolves resource navigation label and breadcrumb', function () {
    app('translator')->addLines([
        UserResource::exposedConventionNamespace() . '.navigation_label' => 'Users',
        UserResource::exposedConventionNamespace() . '.breadcrumb' => 'Users',
    ], 'en');

    expect(UserResource::resolveLabel('navigation_label'))->toBe('Users')
        ->and(UserResource::resolveLabel('breadcrumb'))->toBe('Users');
});

it('resolves page-level title and subheading metadata', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.title' => 'Dashboard',
        'filament/pages/translating-page.subheading' => 'Overview',
    ], 'en');

    expect(TranslatingPage::resolveLabel('title'))->toBe('Dashboard')
        ->and(TranslatingPage::resolveLabel('subheading'))->toBe('Overview');
});

it('returns null for missing null-allowed metadata', function () {
    expect(UserResource::resolveLabel('navigation_label', allowNull: true))->toBeNull()
        ->and(TranslatingPage::resolveLabel('title', allowNull: true))->toBeNull();
});
