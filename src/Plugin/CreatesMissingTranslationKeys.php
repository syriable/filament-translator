<?php

namespace Syriable\Filament\Plugins\Translator\Plugin;

use Closure;
use Illuminate\Support\Str;
use Syriable\Filament\Plugins\Translator\MissingTranslationKeyWriter;

/**
 * Opt-in support for writing missing convention keys back to the application's lang files during
 * local development.
 *
 * @see MissingTranslationKeyWriter
 */
trait CreatesMissingTranslationKeys
{
    protected bool | Closure $createMissingTranslationKeys = false;

    /**
     * @var (Closure(string): string)|null
     */
    protected ?Closure $missingTranslationKeyValueResolver = null;

    /**
     * Enable writing missing *required* convention keys to the lang files. Intended for local
     * development only — the writer always skips production regardless of this flag.
     *
     * @param  bool|(Closure(): bool)  $condition
     * @param  (Closure(string): string)|null  $using  Custom resolver for the seeded value.
     */
    public function createMissingTranslationKeys(bool | Closure $condition = true, ?Closure $using = null): static
    {
        $this->createMissingTranslationKeys = $condition;
        $this->missingTranslationKeyValueResolver = $using;

        return $this;
    }

    public function shouldCreateMissingTranslationKeys(): bool
    {
        return (bool) value($this->createMissingTranslationKeys);
    }

    /**
     * Resolve the value to seed a newly created convention key with.
     */
    public function resolveMissingTranslationKeyValue(string $conventionKey): string
    {
        if ($this->missingTranslationKeyValueResolver !== null) {
            return (string) ($this->missingTranslationKeyValueResolver)($conventionKey);
        }

        return static::defaultMissingTranslationKeyValue($conventionKey);
    }

    /**
     * Humanise the component segment of the key (the segment before the trailing attribute, e.g.
     * `forgot-password` in `…actions.forgot-password.label`), mirroring Filament's default label so
     * the UI is unchanged once the stub is written.
     */
    public static function defaultMissingTranslationKeyValue(string $conventionKey): string
    {
        $path = str_contains($conventionKey, '.')
            ? Str::after($conventionKey, '.')
            : $conventionKey;

        $segments = explode('.', $path);

        $name = count($segments) >= 2
            ? $segments[count($segments) - 2]
            : end($segments);

        return Str::headline((string) $name);
    }
}
