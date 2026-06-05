<?php

namespace Syriable\Filament\Plugins\Translator;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Syriable\Filament\Plugins\Translator\Plugin\ConfiguresPathAliases;
use Syriable\Filament\Plugins\Translator\Plugin\CreatesMissingTranslationKeys;

/**
 * Filament panel plugin entry point for Syriable Filament Translator.
 *
 * Register via {@see make()} on your panel, optionally chain {@see pathAliases()} to remap
 * class namespace fragments (for example `App\Livewire` → `livewire`), or
 * {@see createMissingTranslationKeys()} to scaffold missing lang keys during local development.
 */
class TranslatorPlugin implements Plugin
{
    use ConfiguresPathAliases;
    use CreatesMissingTranslationKeys;

    public static function make(): static
    {
        $plugin = app(static::class);

        $plugin->setUp();

        return $plugin;
    }

    public static function get(?Panel $panel = null): static
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        $id = app(static::class)->getId();

        // Tolerate the plugin not being registered on the panel (e.g. standalone Livewire pages
        // that only boot the registry). Returning a default instance keeps label resolution
        // working with empty path aliases instead of throwing.
        if (! $panel->hasPlugin($id)) {
            return app(static::class);
        }

        /** @var static $plugin */
        $plugin = $panel->getPlugin($id);

        return $plugin;
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
