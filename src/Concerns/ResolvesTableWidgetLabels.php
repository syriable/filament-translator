<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Syriable\Filament\Plugins\Translator\Filament\Widgets\TableWidget;

/**
 * @mixin TableWidget
 */
trait ResolvesTableWidgetLabels
{
    use ResolvesWidgetLabels;

    protected function getTableHeading(): string | Htmlable | null
    {
        return static::resolveLabel('heading', allowNull: true) ?? parent::getTableHeading();
    }
}
