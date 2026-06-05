<?php

use TranslatorFixtures\App\Filament\Clusters\AccountCluster;

/**
 * Issue #60 — Cluster navigation label resolution.
 */
beforeEach(function () {
    $reset = new ReflectionProperty(AccountCluster::class, 'conventionKeyExistenceCache');
    $reset->setAccessible(true);
    $reset->setValue(null, []);
});

it('resolves a cluster navigation label from the convention namespace', function () {
    app('translator')->addLines([
        AccountCluster::exposedConventionNamespace() . '.navigation_label' => 'Account',
    ], 'en');

    expect(AccountCluster::resolveLabel('navigation_label'))->toBe('Account');
});

it('resolves a cluster navigation group from the convention namespace', function () {
    app('translator')->addLines([
        AccountCluster::exposedConventionNamespace() . '.navigation_group' => 'Settings',
    ], 'en');

    expect(AccountCluster::resolveLabel('navigation_group'))->toBe('Settings');
});

it('returns null for a missing null-allowed cluster label', function () {
    expect(AccountCluster::resolveLabel('navigation_label', allowNull: true))->toBeNull();
});
