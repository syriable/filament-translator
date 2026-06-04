<?php

namespace Syriable\Filament\Plugins\Translator\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Syriable\Filament\Plugins\Translator\Tests\Fixtures\TestPanelProvider;
use Syriable\Filament\Plugins\Translator\TranslatorServiceProvider;

class TestCase extends Orchestra
{
    /**
     * Testbench disables Laravel's package auto-discovery, so Filament and its dependencies
     * must be registered manually. Some dependency providers (e.g. blade-capture-directive)
     * are only pulled in by newer Filament releases, so filter to the ones actually installed
     * to keep the `prefer-lowest` matrix green.
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return array_values(array_filter([
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            TranslatorServiceProvider::class,
            TestPanelProvider::class,
        ], static fn (string $provider): bool => class_exists($provider)));
    }
}
