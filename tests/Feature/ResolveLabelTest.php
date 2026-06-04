<?php

use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use TranslatorFixtures\App\Filament\Clusters\AccountCluster;
use TranslatorFixtures\App\Filament\Resources\UserResource;

it('composes a plain key with no page-label context', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.alpha' => 'A',
    ], 'en');

    expect(UserResource::resolveLabel('alpha'))->toBe('A');
});

it('inserts the page-label context value into the key', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.form.beta' => 'B',
    ], 'en');

    expect(UserResource::resolveLabel('beta', pageLabelContext: PageLabelContext::Form))->toBe('B');
});

it('inserts the explicit page-label context key into the key', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.infolist.gamma' => 'C',
    ], 'en');

    expect(UserResource::resolveLabel('gamma', pageLabelContextKey: 'infolist'))->toBe('C');
});

it('prefers the explicit context key over the context enum', function () {
    app('translator')->addLines([
        'filament/resources/user-resource.infolist.delta' => 'D',
    ], 'en');

    expect(UserResource::resolveLabel(
        'delta',
        pageLabelContext: PageLabelContext::Table,
        pageLabelContextKey: 'infolist',
    ))->toBe('D');
});

it('composes a cluster key without any context segment', function () {
    app('translator')->addLines([
        'filament/clusters/account-cluster.navigation_label' => 'Account',
    ], 'en');

    expect(AccountCluster::resolveLabel('navigation_label'))->toBe('Account');
});
