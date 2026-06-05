<?php

namespace TranslatorFixtures\App\Filament\Imports;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesImporterLabels;

/**
 * Fixture importer living under a realistic `App\Filament\Imports\...` namespace so the
 * convention-namespace derivation produces `filament/imports/sample-importer`, letting tests assert
 * column-label resolution beyond the notification strings. The protected namespace helper is
 * re-exposed publicly for assertions.
 */
class SampleImporter
{
    use ResolvesImporterLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
