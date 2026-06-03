<?php

namespace Syriable\Filament\Plugins\Translator;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Syriable\Filament\Plugins\Translator\Plugin\ConfiguresPathAliases;

/**
 * Filament panel plugin entry point for Syriable Filament Translator.
 *
 * Register via {@see make()} on your panel, optionally chain {@see pathAliases()} to remap
 * class namespace fragments (for example `App\Livewire` → `livewire`).
 */
class TranslatorPlugin implements Plugin
{
    use ConfiguresPathAliases;

    public static function make(): static
    {
        $plugin = app(static::class);

        $plugin->setUp();

        return $plugin;
    }

    public static function get(?Panel $panel = null): static
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return $panel->getPlugin(app(static::class)->getId());
    }

    public static function isActive(?Panel $panel = null): bool
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return $panel->hasPlugin(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'syriable/filament-translator';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        app(ConventionRegistry::class)->registerDefaults();
    }

    protected function setUp(): void
    {
        //
    }
}
