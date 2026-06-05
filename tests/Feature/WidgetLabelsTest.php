<?php

use TranslatorFixtures\App\Filament\Widgets\AccountStatsWidget;
use TranslatorFixtures\App\Filament\Widgets\LatestOrdersWidget;
use TranslatorFixtures\App\Filament\Widgets\SalesChartWidget;

/**
 * Issue #61 — Widget label resolution for chart, stats-overview, and table widgets.
 */
beforeEach(function () {
    foreach ([SalesChartWidget::class, AccountStatsWidget::class, LatestOrdersWidget::class] as $class) {
        $reset = new ReflectionProperty($class, 'conventionKeyExistenceCache');
        $reset->setAccessible(true);
        $reset->setValue(null, []);
    }
});

it('resolves a chart widget heading from the convention namespace', function () {
    app('translator')->addLines([
        SalesChartWidget::exposedConventionNamespace() . '.heading' => 'Sales',
    ], 'en');

    expect((new SalesChartWidget())->getHeading())->toBe('Sales');
});

it('resolves stats overview widget heading and description', function () {
    app('translator')->addLines([
        AccountStatsWidget::exposedConventionNamespace() . '.heading' => 'Account stats',
        AccountStatsWidget::exposedConventionNamespace() . '.description' => 'Key account metrics',
    ], 'en');

    $widget = new AccountStatsWidget();

    expect($widget->exposedHeading())->toBe('Account stats')
        ->and($widget->exposedDescription())->toBe('Key account metrics');
});

it('resolves a table widget heading from the convention namespace', function () {
    app('translator')->addLines([
        LatestOrdersWidget::exposedConventionNamespace() . '.heading' => 'Latest orders',
    ], 'en');

    expect((new LatestOrdersWidget())->exposedTableHeading())->toBe('Latest orders');
});

it('returns null for a missing null-allowed widget heading', function () {
    expect((new SalesChartWidget())->getHeading())->toBeNull();
});
