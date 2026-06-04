<?php

namespace TranslatorFixtures\App\Filament\Clusters;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesClusterLabels;

/**
 * Fixture exercising the simpler {@see ResolvesClusterLabels::resolveLabel()} key shape
 * (no page-label context segment).
 */
class AccountCluster
{
    use ResolvesClusterLabels;

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
