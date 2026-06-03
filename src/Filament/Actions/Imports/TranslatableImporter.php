<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Actions\Imports;

use Filament\Actions\Imports\Importer as BaseImporter;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesImporterLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableImporter extends BaseImporter implements TranslatesConventionally
{
    use ResolvesImporterLabels;
}
