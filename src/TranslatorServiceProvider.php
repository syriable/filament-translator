<?php

namespace Syriable\Filament\Plugins\Translator;

use Closure;
use Filament;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Boots Syriable Filament Translator: registers the package and exposes
 * {@see conventionKey()} macros on Filament schema components.
 */
class TranslatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-translator')
            ->hasConfigFile();
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootMacros();
    }

    protected function bootMacros(): void
    {
        $conventionKeyMacro = function (string | Closure $key, bool | Closure $isAbsolute = false): Filament\Support\Components\ViewComponent | Filament\Support\Components\Component {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            $this->conventionKey = $key;
            $this->isConventionKeyAbsolute = $isAbsolute;

            return $this;
        };

        $getConventionKeyMacro = function (): ?string {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate($this->conventionKey ?? null);
        };

        $conventionKeyAbsoluteMacro = function (bool | Closure $condition): static {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            $this->isConventionKeyAbsolute = $condition;

            return $this;
        };

        $isConventionKeyAbsoluteMacro = function (): bool {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate($this->isConventionKeyAbsolute ?? false);
        };

        Filament\Support\Components\ViewComponent::macro('conventionKey', $conventionKeyMacro);
        Filament\Support\Components\ViewComponent::macro('getConventionKey', $getConventionKeyMacro);
        Filament\Support\Components\ViewComponent::macro('conventionKeyAbsolute', $conventionKeyAbsoluteMacro);
        Filament\Support\Components\ViewComponent::macro('isConventionKeyAbsolute', $isConventionKeyAbsoluteMacro);

        Filament\Support\Components\Component::macro('conventionKey', $conventionKeyMacro);
        Filament\Support\Components\Component::macro('getConventionKey', $getConventionKeyMacro);
        Filament\Support\Components\Component::macro('conventionKeyAbsolute', $conventionKeyAbsoluteMacro);
        Filament\Support\Components\Component::macro('isConventionKeyAbsolute', $isConventionKeyAbsoluteMacro);
    }
}
