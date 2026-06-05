<?php

namespace TranslatorFixtures\App\Filament\Widgets;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesStatsOverviewLabels;

/**
 * Fixture stats-overview widget. Exposes the protected heading/description resolvers publicly so
 * the convention key resolution can be asserted without a full widget render.
 */
class AccountStatsWidget
{
    use ResolvesStatsOverviewLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }

    public function exposedHeading(): ?string
    {
        return static::resolveLabel('heading', allowNull: true);
    }

    public function exposedDescription(): ?string
    {
        return static::resolveLabel('description', allowNull: true);
    }
}
