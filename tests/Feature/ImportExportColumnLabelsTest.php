<?php

use TranslatorFixtures\App\Filament\Exports\SampleExporter;
use TranslatorFixtures\App\Filament\Imports\SampleImporter;

/**
 * Issues #66 / #73 — importer/exporter column label resolution beyond notification strings. The
 * column wiring resolves `columns.{name}.label` (and `columns.{name}.example_header` for imports)
 * under the importer/exporter convention namespace.
 */
beforeEach(function () {
    resetTranslatorCaches([SampleImporter::class, SampleExporter::class]);
});

it('resolves an exporter column label from the convention namespace', function () {
    app('translator')->addLines([
        SampleExporter::exposedConventionNamespace() . '.columns.email.label' => 'Email address',
    ], 'en');

    expect(SampleExporter::resolveLabel('columns.email.label'))->toBe('Email address');
});

it('resolves an importer column label and example header', function () {
    app('translator')->addLines([
        SampleImporter::exposedConventionNamespace() . '.columns.email.label' => 'Email address',
        SampleImporter::exposedConventionNamespace() . '.columns.email.example_header' => 'email@example.com',
    ], 'en');

    expect(SampleImporter::resolveLabel('columns.email.label'))->toBe('Email address')
        ->and(SampleImporter::resolveLabel('columns.email.example_header'))->toBe('email@example.com');
});

it('returns null for a missing null-allowed importer column attribute', function () {
    expect(SampleImporter::resolveLabel('columns.email.example_header', allowNull: true))->toBeNull();
});
