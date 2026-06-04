<?php

use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use TranslatorFixtures\App\Filament\Actions\Exports\SampleExporter;
use TranslatorFixtures\App\Filament\Actions\Imports\SampleImporter;

/**
 * The convention-key existence cache is a per-class static that intentionally survives a
 * request (translations don't change mid-request). Reset it between tests so each test can
 * register fresh lines without seeing a stale "missing" result from an earlier test.
 */
function resetConventionCache(string $class): void
{
    $property = new ReflectionProperty($class, 'conventionKeyExistenceCache');
    $property->setAccessible(true);
    $property->setValue(null, []);
}

beforeEach(function () {
    resetConventionCache(SampleImporter::class);
    resetConventionCache(SampleExporter::class);
});

function makeImport(int $total, int $successful): Import
{
    $import = new Import();
    $import->total_rows = $total;
    $import->successful_rows = $successful;

    return $import;
}

function makeExport(int $total, int $successful): Export
{
    $export = new Export();
    $export->total_rows = $total;
    $export->successful_rows = $successful;

    return $export;
}

it('builds a default importer notification body when no translation exists', function () {
    expect(SampleImporter::getCompletedNotificationBody(makeImport(5, 5)))->toBe('5 rows imported.')
        ->and(SampleImporter::getCompletedNotificationBody(makeImport(1, 1)))->toBe('1 row imported.');
});

it('appends a failed-rows sentence to the importer body', function () {
    expect(SampleImporter::getCompletedNotificationBody(makeImport(10, 7)))
        ->toBe('7 rows imported. 3 rows failed to import.');
});

it('uses the translated importer body when available', function () {
    app('translator')->addLines([
        SampleImporter::exposedConventionNamespace() . '.notifications.completed.body.default' => 'Imported :successfulRows!',
    ], 'en');

    expect(SampleImporter::getCompletedNotificationBody(makeImport(7, 7)))->toBe('Imported 7!');
});

it('falls back to a non-empty importer title when no translation exists', function () {
    expect(SampleImporter::getCompletedNotificationTitle(makeImport(3, 3)))->toBeString()->not->toBeEmpty();
});

it('uses the translated importer title when available', function () {
    app('translator')->addLines([
        SampleImporter::exposedConventionNamespace() . '.notifications.completed.title' => 'Import done',
    ], 'en');

    expect(SampleImporter::getCompletedNotificationTitle(makeImport(3, 3)))->toBe('Import done');
});

it('builds a default exporter notification body when no translation exists', function () {
    expect(SampleExporter::getCompletedNotificationBody(makeExport(5, 5)))->toBe('5 rows exported.')
        ->and(SampleExporter::getCompletedNotificationBody(makeExport(10, 7)))
        ->toBe('7 rows exported. 3 rows failed to export.');
});

it('uses the translated exporter body when available', function () {
    app('translator')->addLines([
        SampleExporter::exposedConventionNamespace() . '.notifications.completed.body.default' => 'Exported :successfulRows!',
    ], 'en');

    expect(SampleExporter::getCompletedNotificationBody(makeExport(4, 4)))->toBe('Exported 4!');
});
