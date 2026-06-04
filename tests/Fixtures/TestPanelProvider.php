<?php

namespace Syriable\Filament\Plugins\Translator\Tests\Fixtures;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelProvider;

/**
 * Minimal default panel so {@see Filament::getCurrentOrDefaultPanel()}
 * resolves during tests. No plugin is registered here; tests that need the
 * TranslatorPlugin (e.g. path-alias resolution) register it on the panel themselves.
 */
class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('testing')
            ->path('testing');
    }
}
