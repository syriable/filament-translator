<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Actions\Exports;

use Filament\Actions\Exports\Exporter as BaseExporter;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesExporterLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableExporter extends BaseExporter implements TranslatesConventionally
{
    use ResolvesExporterLabels;
}
