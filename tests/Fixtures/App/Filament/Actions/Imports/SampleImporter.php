<?php

namespace TranslatorFixtures\App\Filament\Actions\Imports;

use Syriable\Filament\Plugins\Translator\Filament\Actions\Imports\TranslatableImporter;

class SampleImporter extends TranslatableImporter
{
    public static function getColumns(): array
    {
        return [];
    }

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
