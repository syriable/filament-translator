<?php

namespace TranslatorFixtures\App\Filament\Actions\Exports;

use Syriable\Filament\Plugins\Translator\Filament\Actions\Exports\TranslatableExporter;

class SampleExporter extends TranslatableExporter
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
