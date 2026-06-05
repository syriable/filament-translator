<?php

namespace TranslatorFixtures\App\Filament\Exports;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesExporterLabels;

/**
 * Fixture exporter living under a realistic `App\Filament\Exports\...` namespace so the
 * convention-namespace derivation produces `filament/exports/sample-exporter`, letting tests assert
 * column-label resolution beyond the notification strings. The protected namespace helper is
 * re-exposed publicly for assertions.
 */
class SampleExporter
{
    use ResolvesExporterLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
