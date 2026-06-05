<?php

namespace Syriable\Filament\Plugins\Translator;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Writes missing convention keys back to the application's lang files.
 *
 * Given a fully-qualified convention key such as
 * `livewire/auth/login.form.components.actions.forgot-password.label`, the part before the first
 * dot (`livewire/auth/login`) selects the lang file (`lang/{locale}/livewire/auth/login.php`) and
 * the remainder (`form.components.actions.forgot-password.label`) is the nested array path. Missing
 * directories, files, and nested arrays are created; existing translations are preserved.
 */
class MissingTranslationKeyWriter
{
    /**
     * Resolver entry point, called on a missing *required* key. Writes the stub (when enabled and
     * not in production) and returns the seeded value so the caller can display it on this request,
     * or null when nothing was written.
     */
    public static function handle(string $conventionKey, ?string $locale = null): ?string
    {
        // A key needs both a group (file) and a nested item to be writable.
        if (! str_contains($conventionKey, '.')) {
            return null;
        }

        // Never touch the filesystem on production requests.
        if (app()->isProduction()) {
            return null;
        }

        $plugin = TranslatorPlugin::get();

        if (! $plugin->shouldCreateMissingTranslationKeys()) {
            return null;
        }

        $locale ??= app()->getLocale();
        $value = $plugin->resolveMissingTranslationKeyValue($conventionKey);

        (new self())->write($conventionKey, $value, $locale);

        return $value;
    }

    /**
     * Ensure the nested key exists in its lang file, creating the file and array path as needed.
     * Existing values are never overwritten. Returns the lang file path.
     */
    public function write(string $conventionKey, string $value, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $group = Str::before($conventionKey, '.');
        $item = Str::after($conventionKey, '.');

        $path = lang_path("{$locale}/{$group}.php");

        // Defend against a malicious convention key (e.g. one set via `conventionKey('../../...')`)
        // escaping the lang directory. The group legitimately contains `/` for nested lang folders,
        // so reject traversal/null bytes and confirm the resolved path stays inside lang_path().
        if (! $this->isPathWithinLang($path)) {
            throw new RuntimeException("Refusing to write translation key outside the lang directory: [{$conventionKey}].");
        }

        $translations = $this->readExisting($path);

        // Preserve an existing value rather than clobbering it.
        if (Arr::has($translations, $item)) {
            return $path;
        }

        Arr::set($translations, $item, $value);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->toPhp($translations));

        // Drop any compiled copy so a subsequent require reads the new contents (CLI/Octane).
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }

        return $path;
    }

    /**
     * Confirm the (not-yet-existing) target file resolves to a location inside `lang_path()`,
     * resolving any `.`/`..` segments lexically so the check works before the file is created.
     */
    protected function isPathWithinLang(string $path): bool
    {
        if (str_contains($path, "\0")) {
            return false;
        }

        $base = $this->canonicalize(lang_path());
        $target = $this->canonicalize($path);

        return $target === $base || str_starts_with($target, $base . '/');
    }

    /**
     * Lexically normalize a filesystem path: convert separators to `/`, drop empty/`.` segments,
     * and resolve `..` segments without touching the filesystem.
     */
    protected function canonicalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $isAbsolute = str_starts_with($path, '/');

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return ($isAbsolute ? '/' : '') . implode('/', $segments);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function readExisting(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $loaded = require $path;

        return is_array($loaded) ? $loaded : [];
    }

    /**
     * @param  array<array-key, mixed>  $translations
     */
    protected function toPhp(array $translations): string
    {
        return "<?php\n\nreturn " . $this->export($translations) . ";\n";
    }

    /**
     * Render an array as short-syntax PHP with 4-space indentation.
     *
     * @param  array<array-key, mixed>  $value
     */
    protected function export(array $value, int $depth = 1): string
    {
        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $depth);
        $lines = [];

        foreach ($value as $key => $item) {
            $exportedKey = is_int($key) ? (string) $key : "'" . $this->escape((string) $key) . "'";

            $exportedValue = is_array($item)
                ? $this->export($item, $depth + 1)
                : $this->scalar($item);

            $lines[] = "{$indent}{$exportedKey} => {$exportedValue},";
        }

        $closingIndent = str_repeat('    ', $depth - 1);

        return "[\n" . implode("\n", $lines) . "\n{$closingIndent}]";
    }

    protected function scalar(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . $this->escape($value) . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return var_export($value, true);
    }

    protected function escape(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
