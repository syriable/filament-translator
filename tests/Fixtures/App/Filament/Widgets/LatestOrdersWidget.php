<?php

namespace TranslatorFixtures\App\Filament\Widgets;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesTableWidgetLabels;

/**
 * Fixture table widget. Exposes the protected table-heading resolver publicly so the convention
 * key resolution can be asserted without a full widget render.
 */
class LatestOrdersWidget
{
    use ResolvesTableWidgetLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }

    public function exposedTableHeading(): ?string
    {
        return static::resolveLabel('heading', allowNull: true);
    }
}
