<?php

namespace Syriable\Filament\Plugins\Translator;

use Closure;
use Filament;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\Filament\Plugins\Translator\Support\ConventionKeyStore;

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
        // Convention-key metadata is stored in a WeakMap keyed by the component instance rather than
        // as dynamic properties, which would trigger the PHP 8.4 dynamic-property deprecation on the
        // third-party Filament component classes these macros are attached to.
        $conventionKeyMacro = function (string | Closure $key, bool | Closure $isAbsolute = false): Filament\Support\Components\ViewComponent | Filament\Support\Components\Component {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            ConventionKeyStore::setKey($this, $key);
            ConventionKeyStore::setAbsolute($this, $isAbsolute);

            return $this;
        };

        $getConventionKeyMacro = function (): ?string {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate(ConventionKeyStore::getKey($this));
        };

        $conventionKeyAbsoluteMacro = function (bool | Closure $condition): static {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            ConventionKeyStore::setAbsolute($this, $condition);

            return $this;
        };

        $isConventionKeyAbsoluteMacro = function (): bool {
            /** @var Filament\Support\Components\ViewComponent|Filament\Support\Components\Component $this */
            return $this->evaluate(ConventionKeyStore::getAbsolute($this));
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
